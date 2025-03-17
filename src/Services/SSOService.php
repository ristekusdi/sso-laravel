<?php

namespace RistekUSDI\SSO\Laravel\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use RistekUSDI\SSO\Laravel\Support\OpenIDConfig;
use RistekUSDI\SSO\PHP\Services\SSOService as BaseSSOService;

class SSOService extends BaseSSOService
{
    use SSOServiceTrait {
        saveToken as private traitSaveToken;
        retrieveToken as private traitRetrieveToken;
        forgetToken as private traitForgetToken;
        validateState as private traitValidateState;
        saveState as private traitSaveState;
        forgetState as private traitForgetState;
    }


    public function __construct()
    {
        $this->baseUrl = trim(Config::get('sso.base_url'), '/');
        $this->realm = Config::get('sso.realm');
        $this->clientId = Config::get('sso.client_id');
        $this->clientSecret = Config::get('sso.client_secret');
        $this->openid = (new OpenIDConfig);
        $this->callbackUrl = URL::route(Config::get('sso.web.routes.callback', 'sso.web.callback'));
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

    public function forgetState()
    {
        return $this->traitForgetState();
    }
}