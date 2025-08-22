<?php

namespace App\Http\Middleware;

use App\Facades\WebSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InitWebSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $callback_route = 'sso/callback';
        if (Auth::guard('imissu-web')->check() && $request->path() === $callback_route) {
            WebSession::init(auth('imissu-web')->user()->client_roles);
        }

        return $response;
    }
}
