<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RistekUSDI\SSO\Exceptions\CallbackException;
use RistekUSDI\SSO\Facades\IMISSUWeb;

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
        IMISSUWeb::forgetToken();

        $url = IMISSUWeb::getLogoutUrl();
        return redirect($url);
    }

    /**
     * SSO callback page
     *
     * @throws CallbackException
     *
     * @return view
     */
    public function callback(Request $request)
    {
        // Check for errors from Keycloak
        if (! empty($request->input('error'))) {
            $error = $request->input('error_description');
            $error = ($error) ?: $request->input('error');

            throw new CallbackException($error);
        }

        // Check given state to mitigate CSRF attack
        $state = $request->input('state');
        if (empty($state) || ! IMISSUWeb::validateState($state)) {
            IMISSUWeb::forgetState();

            throw new CallbackException('Invalid state');
        }

        // Change code for token
        $code = $request->input('code');
        if (! empty($code)) {
            $token = IMISSUWeb::getAccessToken($code);

            if (Auth::guard('imissu-web')->validate($token)) {
                $url = config('sso.redirect_url', '/admin');
                return redirect()->intended($url);
            } else {
                // For case like user doesn't have token
                // or user doesn't have access to certain client app
                throw new CallbackException('Unauthorized', 403);
            }
        }

        return redirect(route('sso.login'));
    }
}
