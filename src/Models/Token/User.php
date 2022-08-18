<?php

namespace RistekUSDI\SSO\Models\Token;

use RistekUSDI\SSO\Models\User as UserModel;

class User extends UserModel
{
    /**
     * Get user roles
     *
     * @see TokenGuard::roles()
     *
     * @return boolean
     */
    public function roles()
    {
        return Auth::guard('imissu-token')->roles();  
    }

    /**
     * Check user has roles
     *
     * @see TokenGuard::hasRole($roles, $resource = '')
     *
     * @param  string|array  $roles
     * @param  string  $resource
     * @return boolean
     */
    public function hasRole($roles, $resource = '')
    {
        return Auth::guard('imissu-token')->hasRole($roles, $resource);
    }

    /**
     * Get list of permission authenticate user
     *
     * @return array
     */
    public function permissions()
    {
        return [];
    }

    /**
     * Check user has permissions
     *
     * @see TokenGuard::hasPermission($permissions)
     *
     * @param  string|array  $permissions
     * @return boolean
     */
    public function hasPermission($permissions)
    {
        return false;
    }
}
