<?php

namespace RistekUSDI\SSO\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class OpenIDConfig
{
    /**
     * Keycloak URL
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Keycloak Realm
     *
     * @var string
     */
    protected $realm;

    /**
     * Keycloak OpenId Configuration
     *
     * @var array
     */
    protected $openid;

    /**
     * Keycloak OpenId Cache Configuration
     *
     * @var array
     */
    protected $cacheOpenid;

    public function __construct()
    {
        if (is_null($this->baseUrl)) {
            $this->baseUrl = trim(Config::get('sso.base_url'), '/');
        }

        if (is_null($this->realm)) {
            $this->realm = Config::get('sso.realm');
        }

        if (is_null($this->cacheOpenid)) {
            $this->cacheOpenid = Config::get('sso.cache_openid', false);
        }
    }

    protected function config()
    {
        $cacheKey = 'sso_web_guard_openid-' . $this->realm . '-' . md5($this->baseUrl);

        // From cache?
        if ($this->cacheOpenid) {
            $configuration = Cache::get($cacheKey, []);

            if (! empty($configuration)) {
                return $configuration;
            }
        }

        // Request if cache empty or not using
        $url = $this->baseUrl . '/realms/' . $this->realm;
        $url = $url . '/.well-known/openid-configuration';

        $configuration = [];

        $response = Http::get($url);

        if ($response->failed()) {
            if ($response->serverError()) {
                throw new \Exception($response->body());
            } else {
                throw new \Exception('[SSO Error] It was not possible to load OpenId configuration: ' . $response->throw());
            }
        }

        $configuration = $response->json();

        // Save cache
        if ($this->cacheOpenid) {
            Cache::put($cacheKey, $configuration);
        }

        return $configuration;
    }

    public function get($name)
    {
        if (! $this->openid) {
            $this->openid = $this->config();
        }

        return Arr::get($this->openid, $name);
    }
}
