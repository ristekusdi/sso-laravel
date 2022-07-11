<?php

namespace RistekUSDI\SSO\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static getLoginUrl()
 * @method static getLogoutUrl()
 * @method static getAccessToken(string $code)
 * @method static getUserProfile(array $credentials)
 * @method static forgetToken()
 */
class IMISSUWeb extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'imissu-web';
    }
}
