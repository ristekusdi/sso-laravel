<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class WebSession extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'websession';
    }
}