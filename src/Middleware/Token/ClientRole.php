<?php 

namespace RistekUSDI\SSO\Laravel\Middleware\Token;

use Closure;
use Illuminate\Support\Facades\Auth;
use RistekUSDI\SSO\Laravel\Exceptions\TokenException;

class ClientRole {
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
            
            // At least minimum of truth is one
            $minimum_truth = 1;
            $total_truth = 0;
            $implode_guards = '';
            foreach ($guards as $guard) {
                $guard = trim($guard, '[]');
                $split_guard = explode(':', $guard);
                $client = $split_guard['0'];
                $roles = explode('|', $split_guard['1']);

                if (Auth::guard('imissu-token')->user()->hasRole($roles, $client)) {
                    $total_truth += 1;
                }
            }
            
            if ($total_truth >= $minimum_truth) {
                return $next($request);
            } else {
                $implode_guards = implode($guards, '');
                throw new TokenException("Pengguna tidak memiliki akses dengan ketentuan {$implode_guards}", 403);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], $th->getCode());
        }
	}
}