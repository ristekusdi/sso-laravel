<?php

namespace RistekUSDI\SSO\Models\Web;

use Auth;
use RistekUSDI\SSO\Models\User as UserModel;

class User extends UserModel
{
    /**
     * Get user roles
     *
     * @see WebGuard::roles()
     *
     * @return array
     */
    public function roles()
    {
        return Auth::guard('imissu-web')->user()->roles;  
    }

    /**
     * Check user has roles
     *
     * @see WebGuard::hasRole($roles, $resource = '')
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
     * @see WebGuard::hasPermission($permissions)
     *
     * @param  string|array  $permissions
     * @return boolean
     */
    public function hasPermission($permissions)
    {
        return Auth::guard('imissu-web')->hasPermission($permissions);
    }
}