<?php

return [
    /**
     * SSO Admin URL
     *
     * Generally https://your-admin-server.com
     */
    'admin_url' => env('SSO_ADMIN_URL', ''),

    /**
     * SSO URL
     *
     * Generally https://your-server.com
     */
    'base_url' => env('SSO_BASE_URL', ''),

    /**
     * SSO Realm
     *
     * Default is master
     */
    'realm' => env('SSO_REALM', 'master'),

    /**
     * The SSO Server realm public key (string).
     *
     * @see SSO >> Realm Settings >> Keys >> RS256 >> Public Key
     */
    'realm_public_key' => env('SSO_REALM_PUBLIC_KEY', null),

    /**
     * SSO Client ID
     *
     * @see SSO >> Clients >> Installation
     */
    'client_id' => env('SSO_CLIENT_ID', null),

    /**
     * SSO Client Secret
     *
     * @see SSO >> Clients >> Installation
     */
    'client_secret' => env('SSO_CLIENT_SECRET', null),

    /**
     * Page to redirect after callback if there's no "intent"
     *
     * @see RistekUSDI\SSO\Controllers\AuthController::callback()
     */
    'redirect_url' => '/',

    /**
     * Routes name config.
     */
    'routes' => [
        'login' => 'sso.login',
        'callback' => 'sso.callback',
        'logout' => 'sso.logout',
    ],

    /**
     * Load guard class.
     */
    'guards' => [
        'web' => RistekUSDI\SSO\Auth\Guard\WebGuard::class,
    ],

    /**
    * GuzzleHttp Client options
    *
    * @link http://docs.guzzlephp.org/en/stable/request-options.html
    */
   'guzzle_options' => [],
];
