<?php

namespace RistekUSDI\SSO\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use RistekUSDI\SSO\Exceptions\CallbackException;
use RistekUSDI\SSO\Facades\SSOWeb;

class AuthController extends Controller
{
    /**
     * Redirect to login
     *
     * @return view
     */
    public function login()
    {
        $url = SSOWeb::getLoginUrl();
        SSOWeb::saveState();

        return redirect($url);
    }

    /**
     * Redirect to logout
     *
     * @return view
     */
    public function logout()
    {
        SSOWeb::forgetToken();

        $url = SSOWeb::getLogoutUrl();
        return redirect($url);
    }

    /**
     * Redirect to register
     *
     * @return view
     */
    public function register()
    {
        $url = SSOWeb::getRegisterUrl();
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
        if (empty($state) || ! SSOWeb::validateState($state)) {
            SSOWeb::forgetState();

            throw new CallbackException('Invalid state');
        }

        // Change code for token
        $code = $request->input('code');
        if (! empty($code)) {
            $token = SSOWeb::getAccessToken($code);

            if (Auth::validate($token)) {
                $url = config('sso.redirect_url', '/admin');
                return redirect()->intended($url);
            }
        }

        return redirect(route('sso.login'));
    }
}
