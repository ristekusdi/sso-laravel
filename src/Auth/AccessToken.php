<?php

namespace RistekUSDI\SSO\Laravel\Auth;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AccessToken
{
    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $refreshToken;

    /**
     * @var string
     */
    protected $idToken;

    /**
     * @var int
     */
    protected $expires;

    /**
     * Constructs an access token.
     *
     * @param array $data The token from Keycloak as array.
     */
    public function __construct($data = [])
    {
        $data = (array) $data;

        if (! empty($data['access_token'])) {
            $this->accessToken = $data['access_token'];
        }

        if (! empty($data['refresh_token'])) {
            $this->refreshToken = $data['refresh_token'];
        }

        if (! empty($data['id_token'])) {
            $this->idToken = $data['id_token'];
        }

        if (! empty($data['expires_in'])) {
            $this->expires = (int) $data['expires_in'];
        }
    }

    /**
     * Get AccessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get RefreshToken
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Get expired date of RefreshToken in timestamp
     * 
     * @return int
     */
    public function getRefreshTokenExpiresAt()
    {
        $exp = $this->parseRefreshToken();
        $exp = $exp['exp'] ?? '';

        return (int) $exp;
    }

    /**
     * Get IdToken
     *
     * @return string
     */
    public function getIdToken()
    {
        return $this->idToken;
    }

    /**
     * Check access token has expired
     *
     * @return boolean
     */
    public function hasExpired()
    {
        $exp = $this->parseAccessToken();
        $exp = $exp['exp'] ?? '';

        return time() >= (int) $exp;
    }

    /**
     * Check the ID Token
     *
     * @throws Exception
     * @return void
     */
    public function validateIdToken($claims)
    {
        $token = $this->parseIdToken();
        if (empty($token)) {
            throw new Exception('ID Token is invalid.', 401);
        }

        $default = array(
            'exp' => 0,
            'aud' => '',
            'iss' => '',
        );

        $token = array_merge($default, $token);
        $claims = array_merge($default, (array) $claims);

        // Validate expiration
        if (time() >= (int) $token['exp']) {
            throw new Exception('ID Token already expired.', 401);
        }

        // Validate issuer
        if (empty($claims['iss']) || $claims['iss'] !== $token['iss']) {
            throw new Exception('Access Token has a wrong issuer: must contain issuer from OpenId.', 422);
        }

        // Validate audience
        $audience = (array) $token['aud'];
        if (empty($claims['aud']) || ! in_array($claims['aud'], $audience, true)) {
            throw new Exception('Access Token has a wrong audience: must contain clientId.', 422);
        }

        if (count($audience) > 1 && empty($token['azp'])) {
            throw new Exception('Access Token has a wrong audience: must contain azp claim.', 422);
        }

        if (! empty($token['azp']) && $claims['aud'] !== $token['azp']) {
            throw new Exception('Access Token has a wrong audience: has azp but is not the clientId.', 422);
        }
    }

    /**
     * Validate sub from ID token
     *
     * @return boolean
     */
    public function validateSub($userSub)
    {
        $sub = $this->parseIdToken();
        $sub = $sub['sub'] ?? '';

        return $sub === $userSub;
    }

    /**
     * Validate token signature with a provided public key.
     * @param string $publicKeyString The RSA256 public key as a string (PEM format)
     * @return bool Whether the token signature is valid
     * @throws Exception If validation fails
     */
    public function validateSignatureWithKey($publicKeyString)
    {
        if (empty($this->getAccessToken())) {
            throw new Exception('Access token is not set.', 401);
        }
        
        try {
            // Get token parts
            $token_parts = explode('.', $this->getAccessToken());
            if (count($token_parts) !== 3) {
                throw new Exception('Invalid token format.', 401);
            }

            // Decode header to get the algorithm
            $header = json_decode($this->base64UrlDecode($token_parts[0]), true);
            $alg = $header['alg'] ?? 'RS256';
            
            // Create a key object for JWT verification
            $key = new Key($this->formatRawKeyToPEM($publicKeyString), $alg);

            // Verify the token
            JWT::decode($this->getAccessToken(), $key);

            return true;
        } catch (\Throwable $th) {
            throw new Exception('Token validation failed: '. $th->getMessage(), 401);
        }
    }

    /**
     * Parse the Access Token
     *
     * @return array
     */
    public function parseAccessToken()
    {
        return $this->parseToken($this->accessToken);
    }

    /**
     * Parse the Refresh Token
     *
     * @return array
     */
    public function parseRefreshToken()
    {
        return $this->parseToken($this->refreshToken);
    }

    /**
     * Parse the Id Token
     *
     * @return array
     */
    public function parseIdToken()
    {
        return $this->parseToken($this->idToken);
    }

    /**
     * Get token (access/refresh/id) data
     *
     * @param string $token
     * @return array
     */
    protected function parseToken($token)
    {
        if (! is_string($token)) {
            return [];
        }

        $token = explode('.', $token);
        $token = $this->base64UrlDecode($token[1]);

        return json_decode($token, true);
    }

    /**
     * Base64UrlDecode string
     *
     * @link https://www.php.net/manual/pt_BR/function.base64-encode.php#103849
     *
     * @param  string $data
     * @return string
     */
    protected function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Format a raw public key string into proper PEM format
     * 
     * @param string $rawKey The raw public key string (without PEM headers/footers)
     * @return string The properly formatted PEM public key
     */
    protected function formatRawKeyToPEM($rawKey)
    {
        // Remove any whitespace or line breaks
        $rawKey = preg_replace('/\s+/', '', $rawKey);
    
        // Add PEM headers and format with proper line breaks (64 characters per line)
        return "-----BEGIN PUBLIC KEY-----\n" . 
            chunk_split($rawKey, 64, "\n") .
            "-----END PUBLIC KEY-----";
    }
}