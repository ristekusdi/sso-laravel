<?php

return [
    /**
     * SSO Url
     *
     * Generally https://your-server.com/auth
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
     * We can cache the OpenId Configuration
     * The result from /realms/{realm-name}/.well-known/openid-configuration
     *
     * @link https://www.keycloak.org/docs/3.2/securing_apps/topics/oidc/oidc-generic.html
     */
    'cache_openid' => env('SSO_CACHE_OPENID', false),

    /**
     * Page to redirect after callback if there's no "intent"
     *
     * @see RistekUSDI\SSO\Controllers\AuthController::callback()
     */
    'redirect_url' => '/',

    /**
     * Page to redirect after logout.
     */
    'redirect_logout' => '/',

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
