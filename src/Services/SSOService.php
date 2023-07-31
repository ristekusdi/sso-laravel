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
    const SSO_SESSION_IMPERSONATE = '_sso_token_impersonate';

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
     * Redirect Url
     *
     * @var string
     */
    protected $redirectUrl;

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
        $this->redirectUrl = Config::get('sso.web.redirect_url', '/');
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

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Retrieve Token from Session
     *
     * @return array|null
     */
    public function retrieveToken()
    {
        if (session()->has(self::SSO_SESSION_IMPERSONATE)) {
            return session()->get(self::SSO_SESSION_IMPERSONATE);
        } else {
            return session()->get(self::SSO_SESSION);
        }
    }

    public function retrieveRegularToken()
    {
        return session()->get(self::SSO_SESSION);
    }

    public function retrieveImpersonateToken()
    {
        return session()->get(self::SSO_SESSION_IMPERSONATE);
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
            session()->put(self::SSO_SESSION_IMPERSONATE, $credentials);
        } else {
            $previous_credentials = $this->retrieveRegularToken();
            // Forget impersonate token session
            // Just in case if impersonate user session revoked even session are not expired
            // Example: impersonate user session revoked from Keycloak Administration console.
            if (!is_null($previous_credentials)) {
                $this->forgetImpersonateToken();
            }
            session()->put(self::SSO_SESSION, $credentials);
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
        if (session()->has(self::SSO_SESSION_IMPERSONATE)) {
            session()->forget(self::SSO_SESSION_IMPERSONATE);
        } else {
            session()->forget(self::SSO_SESSION);
        }
    }

    /**
     * Remove Impersonate Token from Session
     *
     * @return void
     */
    public function forgetImpersonateToken()
    {
        session()->forget(self::SSO_SESSION_IMPERSONATE);
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
    public function getLogoutUrl()
    {
        $token = $this->retrieveToken();

        $decoded_access_token = (new AccessToken($token))->parseAccessToken();

        if (isset($decoded_access_token['impersonator'])) {
            $this->invalidateRefreshToken($token['refresh_token']);
            $this->forgetImpersonateToken();
            return $this->getRedirectUrl();
        } else {
            $this->forgetToken();
            $id_token = isset($token['id_token']) ? $token['id_token'] : null;
            return $this->logout($id_token);
        }
    }

    /**
     * Logout user based on id_token
     *
     * @return string
     */
    public function logout($id_token = null)
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
                throw new Exception('Was not able to get userinfo (not 200)', 404);
            }

            $user = $response->getBody()->getContents();
            $user = json_decode($user, true);

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
    public function refreshTokenIfNeeded($credentials)
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
     * Get credentials (access_token, refresh_token, id_token) of impersonate user.
     *
     * Notes:
     * 1. Enable feature Token Exchange, Fine-Grained Admin Permissions, and Account Management REST API in Keycloak.
     * 2. Register user(s) as impersonator in impersonate scope user permissions.
     *
     * @param username, credentials (access token of impersonator)
     * @return array
     */
    public function impersonateRequest($username, $credentials = array())
    {
        $token = [];

        try {
            $credentials = $this->refreshTokenIfNeeded($credentials);

            $url = (new OpenIDConfig)->get('token_endpoint');

            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];

            $form_params = [
                'client_id' => $this->getClientId(),
                'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
                'requested_token_type' => 'urn:ietf:params:oauth:token-type:refresh_token',
                'requested_subject' => $username,
                'subject_token' => (new AccessToken($credentials))->getAccessToken(),
                // Set scope value to openid to get id_token.
                'scope' => 'openid',
            ];

            if (!empty($this->getClientSecret())) {
                $form_params['client_secret'] = $this->getClientSecret();
            }

            $response = $this->httpClient->request('POST', $url, ['headers' => $headers, 'form_params' => $form_params]);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('User not allowed to impersonate', 401);
            }

            $response_body = $response->getBody()->getContents();
            $token = json_decode($response_body, true);
        } catch (GuzzleException $e) {
            log_exception($e);
        } catch (Exception $e) {
            Log::error('[Keycloak Service] ' . print_r($e->getMessage(), true));
        }

        // Revoke previous impersonate user session if $token is not empty
        if (!empty($token)) {
            $impersonate_user_token = $this->retrieveImpersonateToken();
            if (!empty($impersonate_user_token)) {
                $this->invalidateRefreshToken($impersonate_user_token['refresh_token']);
            }
        }

        return $token;
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
