<?php

namespace RistekUSDI\SSO\Middleware\Web;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleActive
{
    /**
     * Handle if user has role active that matched with list of allowed roles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  $roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $roles = explode('|', ($roles[0] ?? ''));
        if (! Auth::guard('imissu-web')->hasRoleActive($roles)) {
            $roles_str = implode(', ', $roles);
            abort(403, "Hanya peran {$roles_str} yang diijinkan mengakses sumber ini!");
        }

        return $next($request);
    }
}
