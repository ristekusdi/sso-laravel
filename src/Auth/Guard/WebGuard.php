<?php

namespace RistekUSDI\SSO\Auth\Guard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Auth\UserProvider;
use RistekUSDI\SSO\Auth\AccessToken;
use RistekUSDI\SSO\Exceptions\CallbackException;
use RistekUSDI\SSO\Models\User;
use RistekUSDI\SSO\Facades\IMISSUWeb;
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
         * If user doesn't have to certain client app then return false
         */
        $token = new AccessToken($credentials);
        $token = $token->parseAccessToken();
        if (!in_array(Config::get('sso.client_id'), array_keys($token['resource_access']))) {
            return false;
        }

        /**
         * Store the section
         */
        $credentials['refresh_token'] = $credentials['refresh_token'] ?? '';
        IMISSUWeb::saveToken($credentials);

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
        $credentials = IMISSUWeb::retrieveToken();
        if (empty($credentials)) {
            return false;
        }

        $user = IMISSUWeb::getUserProfile($credentials);
        if (empty($user)) {
            IMISSUWeb::forgetToken();
            return false;
        }

        // Provide User
        $user = $this->provider->retrieveByCredentials($user);
        $this->setUser($user);

        return true;
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
     * Check user is authenticated and has a role
     *
     * @param array|string $roles
     * @param string $resource Default is empty: point to client_id
     *
     * @return boolean
     */
    public function hasRole($roles)
    {
        if (! $this->check()) {
            return false;
        }

        return empty(array_diff((array) $roles, $this->roles()));
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

        $token = IMISSUWeb::retrieveToken();

        if (empty($token) || empty($token['access_token'])) {
            return false;
        }

        $token = new AccessToken($token);
        try {
            $response = (new \GuzzleHttp\Client)->request('POST', 
            (new OpenIDConfig)->get('token_endpoint'), [
                'headers' => [
                    'Authorization' => "Bearer {$token->getAccessToken()}"
                ],
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:uma-ticket',
                    'audience' => Config::get('sso.client_id')
                ]
            ]);
            
            if ($response->getStatusCode() !== 200) {
                return [];
            }
            
            $token = new AccessToken(json_decode($response->getBody()->getContents(), true));

            // Introspection permissions
            $response = (new \GuzzleHttp\Client)->request('POST', (new OpenIDConfig)->get('introspection_endpoint'), [
                'auth' => [Config::get('sso.client_id'), Config::get('sso.client_secret')],
                'form_params' => [
                    'token_type_hint' => 'requesting_party_token',
                    'token' => $token->getAccessToken()
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $result = json_decode($response->getBody()->getContents(), true);

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
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return [];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return [];
        }
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
        return !empty(array_intersect((array) $permissions, $this->permissions()));
    }
}
