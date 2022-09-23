<?php 

namespace RistekUSDI\SSO\Middleware\Web;

use Closure;
use Illuminate\Support\Facades\Auth;

class Role {

	/**
	 * Handle if user have role(s) based on list of roles in current client app.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next, ...$roles)
	{	
		// Make sure that we receive array with value is not empty.
        $roles = array_unique(array_filter(explode('|', ($roles[0] ?? ''))));
		if (! Auth::guard('imissu-web')->hasRole($roles)) {
            $roles_str = implode(', ', $roles);
            abort(403, "Pengguna dengan peran {$roles_str} yang diijinkan mengakses sumber ini!");
        }

        return $next($request);
	}
}