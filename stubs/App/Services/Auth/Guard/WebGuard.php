<?php

namespace App\Services\Auth\Guard;

use RistekUSDI\SSO\Auth\Guard\WebGuard as Guard;
use RistekUSDI\SSO\Facades\IMISSUWeb;
use App\Facades\AppSession;

class WebGuard extends Guard
{
    public function authenticate()
    {
        // Get Credentials
        $credentials = IMISSUWeb::retrieveToken();
        if (empty($credentials)) {
            return false;
        }

        $user = IMISSUWeb::getUserProfile($credentials);
        if (empty($user)) {
            IMISSUWeb::forgetToken();
            return false;
        }
        
        /**
         * NOTE
         * Sometimes, you maybe want to bind user data with session.
         * Here's the way.
         */
        $user = AppSession::bindWithExtraData($user);
        
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
        AppSession::changeRoleActive($role_active);
        return true;
    }
}