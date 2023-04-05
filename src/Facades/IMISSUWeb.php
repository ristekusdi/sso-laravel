<?php

namespace RistekUSDI\SSO\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static getLoginUrl()
 * @method static getLogoutUrl()
 * @method static getAccessToken(string $code)
 * @method static getUserProfile(array $credentials)
 * @method static saveToken(array $credentials)
 * @method static forgetToken()
 * @method static refreshTokenIfNeeded(array $credentials)
 * @method static impersonateRequest($username, array $credentials)
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
