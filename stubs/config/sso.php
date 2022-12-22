<?php

return [
    /**
     * Keycloak Admin URL
     *
     * Generally https://your-admin-server.com
     */
    'admin_url' => env('KEYCLOAK_ADMIN_URL', ''),

    /**
     * Keycloak URL
     *
     * Generally https://your-server.com
     */
    'base_url' => env('KEYCLOAK_BASE_URL', ''),

    /**
     * Keycloak Realm
     *
     * Default is master
     */
    'realm' => env('KEYCLOAK_REALM', 'master'),

    /**
     * The Keycloak Server realm public key (string).
     *
     * @see Keycloak >> Realm Settings >> Keys >> RS256 >> Public Key
     */
    'realm_public_key' => env('KEYCLOAK_REALM_PUBLIC_KEY', null),

    /**
     * Keycloak Client ID
     *
     */
    'client_id' => env('KEYCLOAK_CLIENT_ID', null),

    /**
     * SSO Client Secret
     *
     */
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET', null),

    /**
    * GuzzleHttp Client options
    *
    * @link http://docs.guzzlephp.org/en/stable/request-options.html
    */
    'guzzle_options' => [],
    
    'web' => [
        /**
         * Page to redirect after callback if there's no "intent"
         *
         * @see App\Http\Controllers\SSO\Web\AuthController::callback()
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
    ]
];
