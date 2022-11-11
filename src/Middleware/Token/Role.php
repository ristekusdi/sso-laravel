<?php 

namespace RistekUSDI\SSO\Laravel\Middleware\Token;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use RistekUSDI\SSO\Laravel\Exceptions\TokenException;

class Role {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(\Illuminate\Http\Request $request, Closure $next, ...$guards)
	{
        
		try {
            if (!Auth::guard('imissu-token')->check()) {
                throw new TokenException("Unauthenticated", 401);
            }
            
            $roles = explode('|', ($guards[0] ?? ''));
            if (Auth::guard('imissu-token')->user()->hasRole($roles)) {
                return $next($request);
            } else {
                throw new TokenException("Peran {$guards['0']} tidak diijinkan mengakses sumber ini", 403);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], $th->getCode());
        }
	}
}