<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            } else {
                // For case like user doesn't have token
                // or user doesn't have access to certain client app
                throw new CallbackException('Unauthorized', 403);
            }
        }

        return redirect(route('sso.login'));
    }
}
