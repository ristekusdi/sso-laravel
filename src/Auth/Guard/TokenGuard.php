<?php

namespace RistekUSDI\SSO\Laravel\Auth\Guard;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use RistekUSDI\SSO\Laravel\Auth\Token;
use RistekUSDI\SSO\Laravel\Exceptions\TokenException;
use RistekUSDI\SSO\Laravel\Exceptions\UserNotFoundException;
use RistekUSDI\SSO\Laravel\Exceptions\ResourceAccessNotAllowedException;

class TokenGuard implements Guard
{
    private $config;
    private $user;
    private $provider;
    private $decodedToken;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->config = config('sso');
        $this->user = null;
        $this->provider = $provider;
        $this->decodedToken = null;
        $this->request = $request;

        $this->authenticate();
    }

    /**
     * Decode token, validate and authenticate user
     *
     * @return mixed
     */
    private function authenticate()
    {
        try {
            $this->decodedToken = Token::decode($this->request->bearerToken(), $this->config['realm_public_key']);
        } catch (\Exception $e) {
            throw new TokenException($e->getMessage());
        }

        if ($this->decodedToken) {
            $this->validate((array) $this->decodedToken);
        }
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser()
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (is_null($this->user)) {
            return null;
        }
        
        if ($this->config['token']['append_decoded_token']) {
            $this->user->token = $this->decodedToken;
        }
        
        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->id;
        }
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (!$this->decodedToken) {
            return false;
        }

        $this->validateResources();

        // Add roles attribute
        $resource_access = (array)$credentials['resource_access'];
        
        $roles = (array) $resource_access[config('sso.client_id')];
        
        if (!empty($roles)) {
            $roles_array = ['client_roles' => $roles['roles']];
        } else {
            $roles_array = ['client_roles' => []];
        }

        $credentials = array_merge($credentials, $roles_array);

        if ($this->config['token']['load_user_from_database']) {
            $methodOnProvider = $this->config['token']['user_provider_custom_retrieve_method'] ?? null;
            if ($methodOnProvider) {
                $user = $this->provider->{$methodOnProvider}($this->decodedToken, $credentials);
            } else {
                $user = $this->provider->retrieveByCredentials($credentials);
            }      

            if (!$user) {
                throw new UserNotFoundException("User not found. Credentials: " . json_encode($credentials), 404);
            }
        } else {
            $user = $this->provider->retrieveByCredentials($credentials);
        }
        
        $this->setUser($user);

        return true;
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
    }

    /**
     * Validate if authenticated user has a valid resource
     *
     * @return void
     */
    private function validateResources()
    {
        $token_resource_access = array_keys((array)($this->decodedToken->resource_access ?? []));
        $allowed_resources = explode(',', $this->config['client_id']);
        if (count(array_intersect($token_resource_access, $allowed_resources)) == 0) {
            throw new ResourceAccessNotAllowedException("The decoded JWT token has not a valid `resource_access` allowed by API. Allowed resources by API: " . $this->config['allowed_resources'], 403);
        }
    }

    /**
     * Returns full decoded JWT token from athenticated user
     *
     * @return mixed|null
     */
    public function token()
    {
        return json_encode($this->decodedToken);
    }

    /**
     * Get list of role authenticate user
     *
     * @return array
     */
    public function roles()
    {
        if (! $this->check()) {
            return false;
        }

        return $this->user()->roles;
    }

    /**
     * Check if authenticated user has a especific role into resource
     * @param string $roles
     * @param string $resource
     * @return bool
     */
    public function hasRole($roles, $resource)
    {
        return empty(array_diff((array) $roles, $this->roles()));
    }
}
