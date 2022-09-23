<?php 

namespace RistekUSDI\SSO\Middleware\Web;

use Closure;
use Illuminate\Support\Facades\Auth;

class Permission {

	/**
	 * Handle if user have permission(s) based on role active permissions in current client app.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next, ...$permissions)
	{	
		// Make sure that we receive array with value is not empty.
        $permissions = array_unique(array_filter(explode('|', ($permissions[0] ?? ''))));
		if (! Auth::guard('imissu-web')->hasPermission($permissions)) {
            $permissions_str = implode(', ', $permissions);
            abort(403, "Pengguna dengan ijin {$permissions_str} yang diijinkan mengakses sumber ini!");
        }

        return $next($request);
	}
}