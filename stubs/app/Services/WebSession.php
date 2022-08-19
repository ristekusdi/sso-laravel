<?php

namespace App\Services;

class WebSession
{
    public function stick($user)
    {
        $role_active = $this->getRoleActive($user['roles']);
        $role_permissions = $this->getRolePermissions($role_active);
        $data = [
            'role_active' => $role_active,
            'role_permissions' => $role_permissions,
        ];
        
        $user = array_merge($user, $data);
        
        return $user;
    }

    public function getRoleActive($roles = array())
    {
        return (session()->has('role_active')) ? session()->get('role_active') : $roles[0];
    }

    public function changeRoleActive($role_active)
    {
        $this->forgetRoleActive();
        session()->put('role_active', $role_active);
        session()->save();
    }

    public function forgetRoleActive()
    {
        session()->forget('role_active');
        session()->save();
    }

    public function getRolePermissions($role_active)
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
}
