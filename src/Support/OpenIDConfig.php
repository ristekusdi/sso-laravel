<?php

namespace RistekUSDI\SSO\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
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

        try {
            $response = (new \GuzzleHttp\Client())->request('GET', $url);
            $configuration = json_decode($response->getBody()->getContents(), true);
            
            // Save cache
            if ($this->cacheOpenid) {
                Cache::put($cacheKey, $configuration);
            }

            return $configuration;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            echo \GuzzleHttp\Psr7\Message::toString($e->getRequest());
            if ($e->hasResponse()) {
                echo \GuzzleHttp\Psr7\Message::toString($e->getResponse());
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            echo \GuzzleHttp\Psr7\Message::toString($e->getRequest());
            echo \GuzzleHttp\Psr7\Message::toString($e->getResponse());
        }
    }

    public function get($name)
    {
        if (! $this->openid) {
            $this->openid = $this->config();
        }

        return Arr::get($this->openid, $name);
    }
}
