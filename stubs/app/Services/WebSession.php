<?php

namespace App\Services;

class WebSession
{
    public function bind($user)
    {
        $role_active = $this->getRoleActive($user['roles']);
        
        $data = [
            'role_active' => $role_active,
            'role_active_permissions' => $this->getRoleActivePermissions($role_active),
        ];
        
        $user = array_merge($user, $data);
        
        return $user;
    }

    public function getRoleActive($roles = array())
    {
        return (session()->has('role_active')) ? session()->get('role_active') : $roles[0];
    }

    public function getRoleActivePermissions($role_active)
    {
        $permissions = [
            'Admin' => [
                'manage-users',
                'manage-roles',
                'impersonate'
            ],
            'Developer' => [
                'manage-settings',
                'manage-users',
                'manage-roles',
                'impersonate'
            ],
        ];

        $selected_permissions = [];
        foreach ($permissions as $key => $value) {
            if ($key == $role_active) {
                $selected_permissions = $permissions[$key];
            }
        }

        return $selected_permissions;
    }

    public function changeRoleActive($role_active)
    {
        session()->forget('role_active');
        session()->save();
        session()->put('role_active', $role_active);
        session()->save();
    }

    /**
     * Forget selected session from app.
     * Dear maintainer and developers:
     * Please don't use session()->flush() 
     * because it also remove session SSO like access_token and refresh_token!
     * Use session()->forget() instead!
     */
    public function forget()
    {
        session()->forget(['role_active']);
        session()->save();
    }
}
