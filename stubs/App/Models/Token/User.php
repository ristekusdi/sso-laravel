<?php

namespace App\Models\Token;

use RistekUSDI\SSO\Models\Token\User as Model;

class User extends Model
{
    public $custom_fillable = [
        'unud_identifier_id',
        'unud_user_type_id',
        'unud_sso_id'
    ];
}
