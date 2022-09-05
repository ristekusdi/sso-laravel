<?php 

namespace RistekUSDI\SSO\Middleware\Web;

use Closure;
use Illuminate\Support\Facades\Config;

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
        if ($user !== null && $user instanceof \RistekUSDI\SSO\Models\Web\User) {
            return $next($request);
        }

		return redirect()->route(Config::get('sso.routes.login', 'sso.web.login'));
	}

}