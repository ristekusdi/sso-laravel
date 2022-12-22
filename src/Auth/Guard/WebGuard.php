<?php

namespace RistekUSDI\SSO\Laravel\Auth\Guard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\UserProvider;
use RistekUSDI\SSO\Laravel\Auth\AccessToken;
use RistekUSDI\SSO\Laravel\Facades\IMISSUWeb;

class WebGuard implements Guard
{
    /**
     * @var null|Authenticatable|User
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return (bool) $this->user();
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (empty($this->user)) {
            $this->authenticate();
        }

        return $this->user;
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return self
     */
    public function setUser(?Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        $user = $this->user();
        return $user->id ?? null;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials['access_token']) || empty($credentials['id_token'])) {
            throw new \Exception('Credentials must have access_token and id_token!');
        }

        $token = new AccessToken($credentials);
        if (empty($token->getAccessToken())) {
            throw new \Exception('Access Token is invalid.');
        }

        $access_token = $token->parseAccessToken();
        /**
         * If user doesn't have access to certain client app then throw exception
         */
        if (!in_array(config('sso.client_id'), array_keys($access_token['resource_access']))) {
            throw new \Exception('Unauthorized', 403);
        }

        $token->validateIdToken(IMISSUWeb::getClaims());

        // Save credentials if there are no exception throw
        $credentials['refresh_token'] = $credentials['refresh_token'] ?? '';
        IMISSUWeb::saveToken($credentials);

        return $this->authenticate();
    }

    public function hasUser()
    {
        // ...
    }

    /**
     * Try to authenticate the user
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|boolean
     */
    public function authenticate()
    {
        // Get Credentials
        $credentials = IMISSUWeb::retrieveToken();
        if (empty($credentials)) {
            return false;
        }

        $user = IMISSUWeb::getUserProfile($credentials);
        if (empty($user)) {
            IMISSUWeb::forgetToken();
            return false;
        }

        // Get client roles and merge to user info
        $token = new AccessToken($credentials);
        $roles = $token->parseAccessToken()['resource_access'][config('sso.client_id')];
        $user = array_merge($user, ['client_roles' => $roles['roles']]);

        // Provide User
        $user = $this->provider->retrieveByCredentials($user);
        $this->setUser($user);
    }
}
