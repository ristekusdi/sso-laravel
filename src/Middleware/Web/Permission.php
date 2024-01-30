<?php 

namespace RistekUSDI\SSO\Laravel\Middleware\Web;

use Closure;

class Permission {

	/**
	 * Handle to check if user has permission(s) from specific role
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next, ...$permissions)
	{	
		// Make sure that we receive array with value is not empty.
        $permissions = array_unique(array_filter(explode('|', ($permissions[0] ?? ''))));
		if (! auth('imissu-web')->user()->hasPermission($permissions)) {
            abort(403, "Anda tidak diijinkan mengakses fitur ini!");
        }

        return $next($request);
	}
}