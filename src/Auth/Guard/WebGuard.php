<?php

namespace RistekUSDI\SSO\Laravel\Auth\Guard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Log;
use RistekUSDI\SSO\PHP\Auth\AccessToken;
use RistekUSDI\SSO\Laravel\Facades\IMISSUWeb;

class WebGuard implements Guard
{
    /**
     * @var null|Authenticatable|User
     */
    protected $user;

    protected ?UserProvider $provider = null;

    /**
     * Constructor.
     *
     * @param Request $request
     */
    public function __construct(UserProvider $provider)
    {
        $this->provider = $provider;
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

    public function hasUser()
    {
        if (!is_null($this->user()) && $this->user() instanceof \RistekUSDI\SSO\Laravel\Models\Web\User) {
            return true;
        }
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
            throw new \Exception('Credentials must have access_token and id_token!', 422);
        }

        // Validate token signature
        if (empty(config('sso.realm_public_key'))) {
            throw new \Exception('Cannot validate token signature.');
        }

        try {
            (new AccessToken($credentials))->validateSignatureWithKey(config('sso.realm_public_key'));
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage(), $th->getCode());
        }

        $token = new AccessToken($credentials);
        if (empty($token->getAccessToken())) {
            throw new \Exception('Access Token is invalid.', 401);
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

    /**
     * Try to authenticate the user
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function authenticate()
    {
        // Get Credentials
        $credentials = IMISSUWeb::retrieveToken();
        if (empty($credentials)) {
            return null;
        }

        $token = new AccessToken($credentials);
        $user = $token->parseAccessToken();
        
        if ($token->hasExpired()) {
            // NOTE: User needs to log in again in case refresh token has expired.
            if (time() >= $token->getRefreshTokenExpiresAt()) {
                return null;
            }
            IMISSUWeb::forgetToken();
            $credentials = IMISSUWeb::refreshAccessToken($credentials);
            IMISSUWeb::saveToken($credentials);
            $token = new AccessToken($credentials);
            $user = $token->parseAccessToken();
        }

        // We validate token signature here after new token is generated.
        // We do this because the token stored in PHP session, the token may expired early before validate and we cannot take advantage of refresh token case.
        if (empty(config('sso.realm_public_key'))) {
            Log::error('Cannot validate token signature.');
            return null;
        }

        try {
            (new AccessToken($credentials))->validateSignatureWithKey(config('sso.realm_public_key'));
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return null;
        }

        // Get client roles
        $roles = $user['resource_access'][config('sso.client_id')];
        $user = array_merge($user, ['client_roles' => $roles['roles']]);

        // Provide User
        $user = $this->provider->retrieveByCredentials($user);
        $this->setUser($user);
    }
}
