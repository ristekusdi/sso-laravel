<?php 

namespace RistekUSDI\SSO\Laravel\Middleware\Web;

use Closure;

class Authenticate {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$user = auth()->guard('imissu-web')->user();
        if ($user !== null && $user instanceof \RistekUSDI\SSO\Laravel\Models\Web\User) {
            return $next($request);
        }

		if (! $request->expectsJson()) {
			return redirect()->route(config('sso.web.routes.login', 'sso.web.login'));
		} else {
			// Unauthenticated
			return abort('401');
		}
	}

}