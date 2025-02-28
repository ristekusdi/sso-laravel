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
        if (auth()->guard('imissu-web')->hasUser()) {
			return $next($request);
		}

		if (! $request->expectsJson()) {
			return redirect()->route(config('sso.web.routes.login', 'sso.web.login'));
		} else {
			return abort('401');
		}
	}

}