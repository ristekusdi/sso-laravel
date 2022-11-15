<?php

namespace RistekUSDI\SSO\Laravel\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * Constructor
     *
     * @param array $profile Keycloak user info
     */
    public function __construct(array $profile = [])
    {
        foreach ($profile as $key => $value) {
            $this->attributes[ $key ] = $value;
        }
        
        $this->id = $this->getKey();
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->preferred_username;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'preferred_username';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->preferred_username;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        throw new \BadMethodCallException('Unexpected method [getAuthPassword] call');
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        throw new \BadMethodCallException('Unexpected method [getRememberToken] call');
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     */
    public function setRememberToken($value)
    {
        throw new \BadMethodCallException('Unexpected method [setRememberToken] call');
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        throw new \BadMethodCallException('Unexpected method [getRememberTokenName] call');
    }

    public function getUsernameAttribute()
    {
        return $this->attributes['username'] = $this->attributes['preferred_username'];
    }

    public function getIdentifierAttribute()
    {
        return $this->attributes['identifier'] = $this->attributes['given_name'];
    }

    public function getFullIdentityAttribute()
    {
        return $this->attributes['full_identity'] = $this->attributes['name'];
    }
}
