<?php

namespace RistekUSDI\SSO\Middleware;

use Closure;
use Illuminate\Http\Request;
use RistekUSDI\SSO\Exceptions\CanException;

class Can extends Authenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $permissions
     * @return mixed
     */
    public function handle($request, Closure $next, ...$permissions)
    {
        if (! $request->user()->hasPermission($permissions)) {
            return redirect('/')->with('error_message', 'Unauthorized.');
        }

        return $next($request);
    }
}
