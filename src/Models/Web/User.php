<?php

namespace RistekUSDI\SSO\Laravel\Models\Web;

use RistekUSDI\SSO\Laravel\Models\User as UserModel;

class User extends UserModel
{
    /**
     * Check if user has specific role
     * @return boolean
     */
    public function hasRole($role)
    {        
        $roles_attr = $this->getAttribute('roles') ?? [];
        $role_names = array_column($roles_attr, 'name');
        if (is_array($role)) {
            $roles = $role;
            return !empty(array_intersect($role_names , (array) $roles));
        }

        return in_array($role, $role_names) ? true : false;
    }

    /**
     * Check if user has permission(s) from specific role
     * @return boolean
     */
    public function hasPermission($permission)
    {
        $role_permissions = [];
        if (isset($this->getAttribute('role')->permissions)) {
            foreach ($this->getAttribute('role')->permissions as $perm) {
                array_push($role_permissions, $perm);
            }
        }
        
        if (is_array($permission)) {
            $permissions = $permission;

            return !empty(array_intersect((array) $role_permissions, (array) $permissions));
        }

        return in_array($permission, $role_permissions) ? true : false;
    }
}
