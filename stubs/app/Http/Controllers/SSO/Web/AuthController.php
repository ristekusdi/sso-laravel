<?php

namespace App\Http\Controllers\SSO\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use RistekUSDI\SSO\Laravel\Facades\IMISSUWeb;

class AuthController extends Controller
{
    /**
     * Redirect to login
     *
     * @return view
     */
    public function login()
    {
        $url = IMISSUWeb::getLoginUrl();
        IMISSUWeb::saveState();

        return redirect($url);
    }

    /**
     * Redirect to logout
     *
     * @return view
     */
    public function logout()
    {
        $url = IMISSUWeb::getLogoutUrl();
        return redirect($url);
    }

    /**
     * SSO callback
     *
     * @throws abort()
     *
     * @return view
     */
    public function callback(Request $request)
    {
        // Check for errors from Keycloak
        if (! empty($request->input('error'))) {
            $error = $request->input('error_description');
            $error = ($error) ?: $request->input('error');

            abort(401, $error);
        }

        // Check given state to mitigate CSRF attack
        $state = $request->input('state');
        if (empty($state) || ! IMISSUWeb::validateState($state)) {
            IMISSUWeb::forgetState();

            abort(401, 'Invalid state');
        }

        // Change code for token
        $code = $request->input('code');
        if (! empty($code)) {
            $token = IMISSUWeb::getAccessToken($code);

            try {
                auth('imissu-web')->validate($token);
                $url = config('sso.web.redirect_url', '/');
                return redirect($url);
            } catch (\Exception $e) {
                abort($e->getCode(), $e->getMessage());
            }
        }

        return redirect(route('sso.web.login'));
    }
}
