<?php

namespace App\Services\Auth\Guard;

use RistekUSDI\SSO\Auth\Guard\WebGuard as Guard;
use RistekUSDI\SSO\Facades\IMISSUWeb;
use App\Facades\WebSession;

class WebGuard extends Guard
{
    public function authenticate()
    {
        // Get Credentials
        $credentials = IMISSUWeb::retrieveToken();
        if (empty($credentials)) {
            throw new \Exception('Credentials are empty.');
        }

        $user = IMISSUWeb::getUserProfile($credentials);
        if (empty($user)) {
            IMISSUWeb::forgetToken();
            throw new \Exception('User not found.');
        }
        
        /**
         * NOTE
         * Here's the way you want to bind user with data come from database or session.
         */
        $user = WebSession::stick($user);
        
        $user = $this->provider->retrieveByCredentials($user);
        $this->setUser($user);
        
        return true;
    }

    public function permissions()
    {
        if (! $this->check()) {
            return false;
        }

        // role_permission attribute get from $custom_fillable.
        return $this->user()->role_permissions;
    }

    // Ini digunakan untuk mengubah role active dalam session internal aplikasi
    public function changeRoleActive($role_active)
    {
        WebSession::changeRoleActive($role_active);
        return true;
    }
}