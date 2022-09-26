<?php

namespace App\Services\Auth\Guard;

use RistekUSDI\SSO\Auth\Guard\WebGuard as Guard;
use RistekUSDI\SSO\Facades\IMISSUWeb;

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
        
        // TODO: put WebSession::bind($user) here!
        
        $user = $this->provider->retrieveByCredentials($user);
        $this->setUser($user);
        
        return true;
    }

    /**
     * Check if user in a certain role active from roles guard.
     *
     * @param array|string $roles
     *
     * @return boolean
     */
    public function hasRole($roles)
    {
        if (! $this->check()) {
            return false;
        }
        
        if (!empty($roles)) {
            return (in_array($this->user()->getAttribute('role_active'), (array) $roles)) ? true : false;
        } else {
            return true;
        }
    }

    /**
     * Get list of permission in a role active user
     *
     * @return array
     */
    public function permissions()
    {
        if (! $this->check()) {
            return false;
        }

        return $this->user()->getAttribute('role_active_permissions');
    }

    /**
     * Check if user has permission(s) in role active permissions
     *
     * @param array|string $scopes
     *
     * @return boolean
     */
    public function hasPermission($permissions)
    {
        if (! $this->check()) {
            return false;
        }
        
        if (!empty($permissions)) {
            return (array_intersect((array) $permissions, $this->permissions())) ? true : false;
        } else {
            return true;
        }
    }

    // TODO: add changeRoleActive method
}