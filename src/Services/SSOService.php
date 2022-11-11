<?php

namespace RistekUSDI\SSO\Laravel\Services;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use RistekUSDI\SSO\Laravel\Auth\AccessToken;
use RistekUSDI\SSO\Laravel\Support\OpenIDConfig;

class SSOService
{
    /**
     * The Session key for token
     */
    const SSO_SESSION = '_sso_token';

    /**
     * The Session key for state
     */
    const SSO_SESSION_STATE = '_sso_state';

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
     * The HTTP Client
     *
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * The Constructor
     * You can extend this service setting protected variables before call
     * parent constructor to comunicate with Keycloak smoothly.
     *
     * @param ClientInterface $client
     * @return void
     */
    public function __construct(ClientInterface $client)
    {
        $this->baseUrl = trim(Config::get('sso.base_url'), '/');
        $this->realm = Config::get('sso.realm');
        $this->clientId = Config::get('sso.client_id');
        $this->clientSecret = Config::get('sso.client_secret');
        $this->callbackUrl = route(Config::get('sso.web.routes.callback', 'sso.web.callback'));
        $this->state = generate_random_state();
        $this->httpClient = $client;
    }

    /**
     * Return the client id for requests
     *
     * @return string
     */
    protected function getClientId()
    {
        return $this->clientId;
    }

    protected function getClientSecret()
    {
        return $this->clientSecret;
    }

    protected function getRealm()
    {
        return $this->realm;
    }

    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    /**
     * Return the state for requests
     *
     * @return string
     */
    protected function getState()
    {
        return $this->state;
    }

    /**
     * Retrieve Token from Session
     *
     * @return array|null
     */
    public function retrieveToken()
    {
        return session()->get(self::SSO_SESSION);
    }

    /**
     * Save Token to Session
     *
     * @return void
     */
    public function saveToken($credentials)
    {
        session()->put(self::SSO_SESSION, $credentials);
        session()->save();
    }

    /**
     * Remove Token from Session
     *
     * @return void
     */
    public function forgetToken()
    {
        session()->forget(self::SSO_SESSION);
        session()->save();
    }

    /**
     * Validate State from Session
     *
     * @return void
     */
    public function validateState($state)
    {
        $challenge = session()->get(self::SSO_SESSION_STATE);
        return (! empty($state) && ! empty($challenge) && $challenge === $state);
    }

    /**
     * Save State to Session
     *
     * @return void
     */
    public function saveState()
    {
        session()->put(self::SSO_SESSION_STATE, $this->state);
        session()->save();
    }

    /**
     * Remove State from Session
     *
     * @return void
     */
    public function forgetState()
    {
        session()->forget(self::SSO_SESSION_STATE);
        session()->save();
    }

    /**
     * Return the login URL
     *
     * @link https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowAuth
     *
     * @return string
     */
    public function getLoginUrl()
    {
        $url = (new OpenIDConfig)->get('authorization_endpoint');
        $params = [
            'scope' => 'openid',
            'response_type' => 'code',
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getCallbackUrl(),
            'state' => $this->getState(),
        ];

        return build_url($url, $params);
    }

    /**
     * Return the logout URL
     *
     * @return string
     */
    public function getLogoutUrl($id_token = null)
    {
        $url = (new OpenIDConfig)->get('end_session_endpoint');

        $params = [
            'client_id' => $this->getClientId(),
        ];

        if ($id_token !== null) {
            $params['id_token_hint'] = $id_token;
            $params['post_logout_redirect_uri'] = url('/');
        }

        return build_url($url, $params);
    }

    /**
     * Get access token from Code
     *
     * @param  string $code
     * @return array
     */
    public function getAccessToken($code)
    {
        $url = (new OpenIDConfig)->get('token_endpoint');
        $params = [
            'code' => $code,
            'client_id' => $this->getClientId(),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getCallbackUrl(),
        ];

        if (! empty($this->getClientSecret())) {
            $params['client_secret'] = $this->getClientSecret();
        }

        $token = [];

        try {
            $response = $this->httpClient->request('POST', $url, ['form_params' => $params]);

            if ($response->getStatusCode() === 200) {
                $token = $response->getBody()->getContents();
                $token = json_decode($token, true);
            }
        } catch (GuzzleException $e) {
            log_exception($e);
        }

        return $token;
    }

    /**
     * Refresh access token
     *
     * @param  string $refreshToken
     * @return array
     */
    public function refreshAccessToken($credentials)
    {
        if (empty($credentials['refresh_token'])) {
            return [];
        }

        $url = (new OpenIDConfig)->get('token_endpoint');
        $params = [
            'client_id' => $this->getClientId(),
            'grant_type' => 'refresh_token',
            'refresh_token' => $credentials['refresh_token'],
            'redirect_uri' => $this->getCallbackUrl(),
        ];

        if (! empty($this->getClientSecret())) {
            $params['client_secret'] = $this->getClientSecret();
        }

        $token = [];

        try {
            $response = $this->httpClient->request('POST', $url, ['form_params' => $params]);

            if ($response->getStatusCode() === 200) {
                $token = $response->getBody()->getContents();
                $token = json_decode($token, true);
            }
        } catch (GuzzleException $e) {
            log_exception($e);
        }

        return $token;
    }

    /**
     * Invalidate Refresh
     *
     * @param  string $refreshToken
     * @return array
     */
    public function invalidateRefreshToken($refreshToken)
    {
        $url = (new OpenIDConfig)->get('end_session_endpoint');
        $params = [
            'client_id' => $this->getClientId(),
            'refresh_token' => $refreshToken,
        ];

        if (! empty($this->getClientSecret())) {
            $params['client_secret'] = $this->getClientSecret();
        }

        try {
            $response = $this->httpClient->request('POST', $url, ['form_params' => $params]);
            return $response->getStatusCode() === 204;
        } catch (GuzzleException $e) {
            log_exception($e);
        }

        return false;
    }

    /**
     * Get access token from Code
     * @param  array $credentials
     * @return array
     */
    public function getUserProfile($credentials)
    {
        $credentials = $this->refreshTokenIfNeeded($credentials);

        $user = [];
        try {
            // Validate JWT Token
            $token = new AccessToken($credentials);

            // Get userinfo
            $url = (new OpenIDConfig)->get('userinfo_endpoint');
            $headers = [
                'Authorization' => 'Bearer ' . $token->getAccessToken(),
                'Accept' => 'application/json',
            ];

            $response = $this->httpClient->request('GET', $url, ['headers' => $headers]);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('Was not able to get userinfo (not 200)');
            }

            $user = $response->getBody()->getContents();
            $user = json_decode($user, true);

            // Get client roles
            $roles = ['roles' => []];
            $roles = $token->parseAccessToken()['resource_access'][$this->getClientId()];
            $user = array_merge($user, ['client_roles' => $roles['roles']]);
            
            // Validate retrieved user is owner of token
            if (! $token->validateSub($user['sub'] ?? '')) {
                throw new Exception("This user is not the owner of token.", 401);
            }
        } catch (GuzzleException $e) {
            log_exception($e);
        } catch (Exception $e) {
            Log::error('[Keycloak Service] ' . print_r($e->getMessage(), true));
        }

        return $user;
    }

    /**
     * Check we need to refresh token and refresh if needed
     *
     * @param  array $credentials
     * @return array
     */
    protected function refreshTokenIfNeeded($credentials)
    {
        if (! is_array($credentials) || empty($credentials['access_token']) || empty($credentials['refresh_token'])) {
            return $credentials;
        }

        $token = new AccessToken($credentials);
        if (! $token->hasExpired()) {
            return $credentials;
        }

        $credentials = $this->refreshAccessToken($credentials);

        if (empty($credentials['access_token'])) {
            $this->forgetToken();
            return [];
        }

        $this->saveToken($credentials);
        return $credentials;
    }

    /**
     * Get claims based on client id and issuer
     */
    public function getClaims()
    {
        return array(
            'aud' => $this->getClientId(),
            'iss' => (new OpenIDConfig)->get('issuer'),
        );
    }
}
