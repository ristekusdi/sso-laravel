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

        /**
        * GuzzleHttp Client options
        *
        * @link http://docs.guzzlephp.org/en/stable/request-options.html
        */
        'guzzle_options' => [],
    ],
    'token' => [
        'load_user_from_database' => false,

        'user_provider_custom_retrieve_method' => null,

        'user_provider_credential' => 'username',

        'token_principal_attribute' => 'preferred_username',

        'append_decoded_token' => false,

        'allowed_resources' => null
    ]
];
