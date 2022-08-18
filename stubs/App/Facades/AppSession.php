<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class AppSession extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'appsession';
    }
}