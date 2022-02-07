<?php

namespace RistekUSDI\SSO\Models\Web;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

class User extends Model implements Authenticatable
{
    /**
     * Attributes we retrieve from Profile
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'sub',
        'preferred_username',
        'given_name',
        'family_name',
        'roles'
    ];

    /**
     * Attributes that developers can add as a custom attribute.
     * Then, it will be merge with fillable property.
     */
    public $custom_fillable = [];

    /**
     * User attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Constructor
     *
     * @param array $profile Keycloak user info
     */
    public function __construct(array $profile)
    {
        $this->fillable = array_merge($this->fillable, $this->custom_fillable);
        foreach ($profile as $key => $value) {
            if (in_array($key, $this->fillable)) {
                switch ($key) {
                    case 'name':
                        $key = 'full_identity';
                        break;
                    case 'family_name':
                        $key = 'name';
                        break;
                    case 'given_name':
                        $key = 'identifier';
                        break;
                    case 'preferred_username':
                        $key = 'username';
                        break;
                }
                $this->attributes[ $key ] = $value;
            }
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
        return $this->username;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->username;
    }

    /**
     * Get user roles
     *
     * @see WebGuard::roles()
     *
     * @return boolean
     */
    public function roles()
    {
        return Auth::guard('imissu-web')->roles();  
    }

    /**
     * Check user has roles
     *
     * @see WebGuard::hasRole()
     *
     * @param  string|array  $roles
     * @param  string  $resource
     * @return boolean
     */
    public function hasRole($roles, $resource = '')
    {
        return Auth::guard('imissu-web')->hasRole($roles, $resource);
    }

    /**
     * Get list of permission authenticate user
     *
     * @return array
     */
    public function permissions()
    {
        return Auth::guard('imissu-web')->permissions();
    }

    /**
     * Check user has permissions
     *
     * @see WebGuard::hasPermission()
     *
     * @param  string|array  $permissions
     * @return boolean
     */
    public function hasPermission($permissions)
    {
        return Auth::guard('imissu-web')->hasPermission($permissions);
    }

    /**
     * Get the password for the user.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getAuthPassword()
    {
        throw new \BadMethodCallException('Unexpected method [getAuthPassword] call');
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getRememberToken()
    {
        throw new \BadMethodCallException('Unexpected method [getRememberToken] call');
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     * @codeCoverageIgnore
     */
    public function setRememberToken($value)
    {
        throw new \BadMethodCallException('Unexpected method [setRememberToken] call');
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getRememberTokenName()
    {
        throw new \BadMethodCallException('Unexpected method [getRememberTokenName] call');
    }
}
