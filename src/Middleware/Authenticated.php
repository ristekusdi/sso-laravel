<?php

namespace RistekUSDI\SSO\Middleware;

use Illuminate\Auth\Middleware\Authenticate;

class Authenticated extends Authenticate
{
    /**
     * Redirect user if it's not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        return route('sso.login');
    }
}
