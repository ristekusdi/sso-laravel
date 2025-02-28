<?php

namespace RistekUSDI\SSO\Laravel\Services;

use Illuminate\Support\Facades\Log;
use RistekUSDI\SSO\Laravel\Auth\AccessToken;

trait SSOServiceTrait
{
    /**
     * The Session key for token
     */
    protected $sso_token = '_sso_token';
    protected $sso_token_impersonate = '_sso_token_impersonate';

    /**
     * The Session key for state
     */
    protected $sso_state = '_sso_state';

    protected function logError($message)
    {
        Log::error("SSO Service error: {$message}");
    }

    /**
     * Retrieve Token from Session
     *
     * @return array|null
     */
    public function retrieveToken()
    {
        if (session()->has($this->sso_token_impersonate)) {
            return session()->get($this->sso_token_impersonate);
        } else {
            return session()->get($this->sso_token);
        }
    }

    /**
     * Save Token to Session
     *
     * @return void
     */
    public function saveToken($credentials)
    {
        $decoded_access_token = (new AccessToken($credentials))->parseAccessToken();
        if (isset($decoded_access_token['impersonator'])) {
            session()->put($this->sso_token_impersonate, $credentials);
        } else {
            $previous_credentials = $this->retrieveRegularToken();
            // Forget impersonate token session
            // Just in case if impersonate user session revoked even session are not expired
            // Example: impersonate user session revoked from Keycloak Administration console.
            if (!is_null($previous_credentials)) {
                $this->forgetImpersonateToken();
            }
            session()->put($this->sso_token, $credentials);
        }
        session()->save();
    }

    /**
     * Remove Token from Session
     *
     * @return void
     */
    public function forgetToken()
    {
        if (session()->has($this->sso_token_impersonate)) {
            $this->forgetImpersonateToken();
        } else {
            session()->forget($this->sso_token);
        }
    }

    public function retrieveRegularToken()
    {
        return session()->get($this->sso_token);
    }

    public function retrieveImpersonateToken()
    {
        return session()->get($this->sso_token_impersonate);
    }

    /**
     * Remove Impersonate Token from Session
     *
     * @return void
     */
    public function forgetImpersonateToken()
    {
        session()->forget($this->sso_token_impersonate);
    }

    /**
     * Validate State from Session
     *
     * @return void
     */
    public function validateState($state)
    {
        $challenge = session()->get($this->sso_state);
        return (! empty($state) && ! empty($challenge) && $challenge === $state);
    }

    /**
     * Save State to Session
     *
     * @return void
     */
    public function saveState()
    {
        session()->put($this->sso_state, $this->getState());
        session()->save();
    }

    /**
     * Remove State from Session
     *
     * @return void
     */
    public function forgetState()
    {
        session()->forget($this->sso_state);
    }
}