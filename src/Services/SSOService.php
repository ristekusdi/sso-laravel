<?php

namespace RistekUSDI\SSO\Laravel\Services;

use Illuminate\Support\Facades\Config;
use RistekUSDI\SSO\PHP\Services\SSOService as PhpSsoService;

class SSOService extends PhpSsoService
{
    use SSOServiceTrait {
        saveToken as private traitSaveToken;
        retrieveToken as private traitRetrieveToken;
        forgetToken as private traitForgetToken;
        validateState as private traitValidateState;
        saveState as private traitSaveState;
        forgetState as private traitForgetState;
    }

    /**
     * Keycloak URL
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Keycloak Realm
     *
     * @var string
     */
    protected $realm;

    /**
     * Keycloak Client ID
     *
     * @var string
     */
    protected $clientId;

    /**
     * Keycloak Client Secret
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * Keycloak OpenId Configuration
     *
     * @var array
     */
    protected $openid;

    /**
     * CallbackUrl
     *
     * @var array
     */
    protected $callbackUrl;

    /**
     * The state for authorization request
     *
     * @var string
     */
    protected $state;

    /**
     * Redirect Url
     *
     * @var string
     */
    protected $redirectUrl;
    
    public function __construct()
    {
        $this->baseUrl = trim(Config::get('sso.base_url'), '/');
        $this->realm = Config::get('sso.realm');
        $this->clientId = Config::get('sso.client_id');
        $this->clientSecret = Config::get('sso.client_secret');
        $this->callbackUrl = route(Config::get('sso.web.routes.callback', 'sso.web.callback'));
        $this->redirectUrl = Config::get('sso.web.redirect_url', '/');
        $this->state = generate_random_state();
    }

    public function saveToken($credentials)
    {
        return $this->traitSaveToken($credentials);
    }

    public function retrieveToken()
    {
        return $this->traitRetrieveToken();
    }

    public function forgetToken()
    {
        return $this->traitForgetToken();
    }

    public function validateState($state)
    {
        return $this->traitValidateState($state);
    }

    public function saveState()
    {
        return $this->traitSaveState();
    }

    public function fotgetState()
    {
        return $this->traitForgetState();
    }
}
