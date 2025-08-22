<?php

namespace RistekUSDI\SSO\Laravel\Support;

use Illuminate\Support\Facades\Config;
use RistekUSDI\SSO\PHP\Support\OpenIDConfig as BaseOpenIDConfig;

class OpenIDConfig extends BaseOpenIDConfig
{
    use OpenIDConfigTrait;

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
}
