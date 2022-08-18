<?php 

namespace RistekUSDI\SSO\Middleware\Web;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use RistekUSDI\SSO\Exceptions\KeycloakGuardException;

class Role {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next, ...$guards)
	{
		if (empty($guards) && Auth::guard('imissu-web')->check()) {
            return $next($request);
        }
		
		// Role active
		$role_active = isset(Auth::guard('imissu-web')->user()->role_active) ? Auth::guard('imissu-web')->user()->role_active : null;
        $guards = explode('|', ($guards[0] ?? ''));
		
		try {
			if (in_array($role_active, $guards)) {
				return $next($request);
			} else {
				throw new KeycloakGuardException("Hanya peran {$guards['0']} yang diijinkan mengakses sumber ini!", 403);
			}
		} catch (\Throwable $th) {
			throw $th;
		}

        return redirect()->route(Config::get('sso.web.routes.login', 'sso.web.login'));
	}
}