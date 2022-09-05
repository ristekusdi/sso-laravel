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
		
		$roles = isset(Auth::guard('imissu-web')->user()->roles) ? Auth::guard('imissu-web')->user()->roles : null;
        $guards = explode('|', ($guards[0] ?? ''));
		
		try {
			if (array_intersect($roles, $guards)) {
				return $next($request);
			} else {
				$guards_str = implode(', ', $guards);
				throw new KeycloakGuardException("Hanya peran {$guards_str} yang diijinkan mengakses sumber ini!", 403);
			}
		} catch (\Throwable $th) {
			throw $th;
		}

        return redirect()->route(Config::get('sso.web.routes.login', 'sso.web.login'));
	}
}