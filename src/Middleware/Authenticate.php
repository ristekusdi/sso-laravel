<?php 

namespace RistekUSDI\SSO\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Config;

class Authenticate {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

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

		return redirect()->route(Config::get('sso.routes.login', 'sso.login'));
	}

}