<?php

namespace App\Services;

class WebSession
{
    public function init($client_roles)
    {   
        $roles = [];
        foreach ($client_roles as $role_name) {
            // NOTE: You may do database query to get a role based on $role_name.
            $role = new \stdClass();
            $role->name = $role_name;

            // NOTE: You may do database query list of permissions based on role id.
            $role_permissions = [
                [
                    'name' => 'dashboard.view',
                ],
            ];
            $role->permissions = array_column($role_permissions, 'name');
            $roles[] = $role;
        }

        $roles = json_decode(json_encode($roles));
        session()->put('roles', $roles);
        session()->save(); // Make session persistent
        session()->put('role', $roles['0']);
        session()->save(); // Make session persistent

        // NOTE: This is an example if you want to add additional session like selected year.
        session()->put('selected_year', date('Y'));
        session()->save(); // Make session persistent
    }

    public function getRoles()
    {
        return session()->get('roles');
    }

    public function setRole($role)
    {
        session()->forget('role');
        session()->put('role', $role);
        session()->save(); // Make session persistent
    }

    public function getRole()
    {
        return session()->get('role');
    }

    public function setSelectedYear($year)
    {
        session()->forget('selected_year');
        session()->put('selected_year', $year);
        session()->save(); // Make session persistent
    }

    // NOTE: This is an example if you want to add additional session like selected year.
    public function getSelectedYear()
    {
        return session()->get('selected_year');
    }
}
