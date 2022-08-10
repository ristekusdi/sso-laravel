<?php 

namespace RistekUSDI\SSO\Middleware\Token;

use Closure;
use Illuminate\Support\Facades\Config;
use RistekUSDI\SSO\Auth\Token;

class Authenticate {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(\Illuminate\Http\Request $request, Closure $next)
	{
        try {
            $user = auth()->guard('imissu-token')->user();
            if ($user !== null && $user instanceof \RistekUSDI\SSO\Models\Token\User) {
                return $next($request);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], $th->getCode());
        }
	}

}