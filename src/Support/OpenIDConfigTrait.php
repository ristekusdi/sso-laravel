<?php

namespace RistekUSDI\SSO\Laravel\Support;

use Illuminate\Support\Facades\Cache;

trait OpenIDConfigTrait {
    
    public function getCache($key, $default = null)
    {
        if ($this->hasCache($key)) {
            return Cache::get($key);
        }
        return $default;
    }

    public function putCache($key, $value, $ttl = 3600)
    {
        Cache::put($key, $value, $ttl);
    }
}