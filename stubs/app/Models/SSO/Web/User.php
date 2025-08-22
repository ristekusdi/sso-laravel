<?php

namespace App\Models\SSO\Web;

use App\Facades\WebSession;
use RistekUSDI\SSO\Laravel\Models\Web\User as Model;

class User extends Model
{
    protected $appends = ['roles', 'role', 'selected_year'];

    public function getRolesAttribute()
    {
        return $this->attributes['roles'] = WebSession::getRoles();
    }

    public function setRoleAttribute($role)
    {
        WebSession::setRole($role);
        $this->attributes['role'] = WebSession::getRole();
    }

    public function getRoleAttribute()
    {
        return $this->attributes['role'] = WebSession::getRole();
    }

    public function setSelectedYearAttribute($value)
    {
        WebSession::setSelectedYear($value);
        $this->attributes['selected_year'] = WebSession::getSelectedYear();
    }

    public function getSelectedYearAttribute()
    {
        return $this->attributes['selected_year'] = WebSession::getSelectedYear();
    }
}
