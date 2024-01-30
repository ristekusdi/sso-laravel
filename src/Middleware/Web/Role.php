<?php 

namespace RistekUSDI\SSO\Laravel\Middleware\Web;

use Closure;

class Role {

	/**
	 * Handle to check if user has specific role
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next, ...$roles)
	{	
		// Make sure that we receive array with value is not empty.
        $roles = array_unique(array_filter(explode('|', ($roles[0] ?? ''))));
		if (! auth('imissu-web')->user()->hasRole($roles)) {
            abort(403, "Anda tidak diijinkan mengakses fitur ini!");
        }

        return $next($request);
	}
}