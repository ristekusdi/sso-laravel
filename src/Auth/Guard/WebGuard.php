<?php

namespace RistekUSDI\SSO\Auth\Guard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Auth\UserProvider;
use RistekUSDI\SSO\Auth\AccessToken;
use RistekUSDI\SSO\Exceptions\CallbackException;
use RistekUSDI\SSO\Models\User;
use RistekUSDI\SSO\Facades\SSOWeb;
use RistekUSDI\SSO\Support\OpenIDConfig;

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
     * @return void
     */
    public function setUser(?Authenticatable $user)
    {
        $this->user = $user;
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
     * @throws BadMethodCallException
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials['access_token']) || empty($credentials['id_token'])) {
            return false;
        }

        /**
         * Store the section
         */
        $credentials['refresh_token'] = $credentials['refresh_token'] ?? '';
        SSOWeb::saveToken($credentials);

        return $this->authenticate();
    }

    /**
     * Try to authenticate the user
     *
     * @throws CallbackException
     * @return boolean
     */
    public function authenticate()
    {
        // Get Credentials
        $credentials = SSOWeb::retrieveToken();
        if (empty($credentials)) {
            return false;
        }

        $user = SSOWeb::getUserProfile($credentials);
        if (empty($user)) {
            SSOWeb::forgetToken();
            return false;
        }

        // Provide User
        $user = $this->provider->retrieveByCredentials($user);
        $this->setUser($user);

        return true;
    }

    /**
     * Check user is authenticated and has a role
     *
     * @param array|string $roles
     * @param string $resource Default is empty: point to client_id
     *
     * @return boolean
     */
    public function hasRole($roles, $resource = '')
    {
        if (empty($resource)) {
            $resource = Config::get('sso.client_id');
        }

        if (! $this->check()) {
            return false;
        }

        $token = SSOWeb::retrieveToken();

        if (empty($token) || empty($token['access_token'])) {
            return false;
        }

        $token = new AccessToken($token);
        $token = $token->parseAccessToken();

        $resourceRoles = $token['resource_access'] ?? [];
        $resourceRoles = $resourceRoles[ $resource ] ?? [];
        $resourceRoles = $resourceRoles['roles'] ?? [];

        return empty(array_diff((array) $roles, $resourceRoles));
    }

    /**
     * Get list of permission authenticate user
     *
     * @return array
     */
    public function permissions()
    {
        if (! $this->check()) {
            return false;
        }

        $token = SSOWeb::retrieveToken();

        if (empty($token) || empty($token['access_token'])) {
            return false;
        }

        $token = new AccessToken($token);

        $response = Http::withToken($token->getAccessToken())->asForm()
        ->post((new OpenIDConfig)->get('token_endpoint'), [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:uma-ticket',
            'audience' => Config::get('sso.client_id')
        ]);
        
        if ($response->failed()) {
            return [];
        }

        $token = new AccessToken($response->json());

        // Introspection permissions
        $response = Http::withBasicAuth(Config::get('sso.client_id'), Config::get('sso.client_secret'))
        ->asForm()->post((new OpenIDConfig)->get('token_introspection_endpoint'), [
            'token_type_hint' => 'requesting_party_token',
            'token' => $token->getAccessToken()
        ]);

        if ($response->failed()) {
            return [];
        }

        $result = $response->json();

        // If permissions don't active then return false
        if (!$result['active']) {
            return [];
        }

        $resourcePermissions = [];
        foreach ($result['permissions'] as $permission) {
            if (!empty($permission['resource_scopes'])) {
                foreach ($permission['resource_scopes'] as $value) {
                    $resourcePermissions[] = $value;
                }
            }
        }

        return $resourcePermissions;
    }

    /**
     * Check user is authenticated and has a permission(s)
     *
     * @param array|string $scopes
     *
     * @return boolean
     */
    public function hasPermission($permissions)
    {
        return empty(array_diff((array) $permissions, $this->permissions()));
    }
}
