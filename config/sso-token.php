<?php

return [
    /**
     * The SSO Server realm public key (string).
     *
     * @see SSO >> Realm Settings >> Keys >> RS256 >> Public Key
     */
    'realm_public_key' => env('SSO_REALM_PUBLIC_KEY', null),
    
    'load_user_from_database' => env('SSO_LOAD_USER_FROM_DATABASE', true),

    'user_provider_custom_retrieve_method' => null,

    'user_provider_credential' => env('SSO_USER_PROVIDER_CREDENTIAL', 'username'),

    'token_principal_attribute' => env('SSO_TOKEN_PRINCIPAL_ATTRIBUTE', 'preferred_username'),

    'append_decoded_token' => env('SSO_APPEND_DECODED_TOKEN', false),

    'allowed_resources' => env('SSO_ALLOWED_RESOURCES', null)
];