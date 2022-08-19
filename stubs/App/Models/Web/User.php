<?php

namespace App\Models\Web;

use RistekUSDI\SSO\Models\Web\User as Model;

class User extends Model
{
    public $custom_fillable = [
        'unud_identifier_id',
        'unud_user_type_id',
        'unud_sso_id',
        'role_active',
        'role_permissions'
    ];
}
