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
    * GuzzleHttp Client options
    *
    * @link http://docs.guzzlephp.org/en/stable/request-options.html
    */
    'guzzle_options' => [],

    /**
     * User attributes
     * 
     * List of additional user attributes from Keycloak or created by your self
     * that you want to load in auth user model (Web and Token guard)
     * @see RistekUSDI\SSO\Models\User::_construct()
     * 
     */
    'user_attributes' => [
        // default attributes
        'unud_identifier_id',
        'unud_sso_id',
        'unud_user_type_id',
        'role_active',
        'role_active_permissions',
    ],
    
    'web' => [
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
            'login' => 'sso.web.login',
            'callback' => 'sso.web.callback',
            'logout' => 'sso.web.logout',
        ],

        /**
         * Load web guard class.
         */
        'guard' => RistekUSDI\SSO\Auth\Guard\WebGuard::class,
    ],
];
