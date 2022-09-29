<?php 

namespace RistekUSDI\SSO\Laravel\Middleware\Web;

use Closure;

class Role {

	/**
	 * Handle if user have role(s) based on list of roles current client app.
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
            $roles_str = implode(', ', $roles);
            abort(403, "Pengguna dengan peran {$roles_str} yang diijinkan mengakses sumber ini!");
        }

        return $next($request);
	}
}