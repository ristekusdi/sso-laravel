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
        $user = auth('imissu-web')->user();
        if ($user !== null && $user instanceof \RistekUSDI\SSO\Laravel\Models\Web\User) {
            return redirect()->intended(config('sso.web.redirect_url', '/'));
        }
        
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
        // NOTE: Forget token to prevent misuse
        IMISSUWeb::forgetToken();
        // NOTE: flush session after getLogoutUrl
        // Otherwise, id_token will be deleted.
        session()->flush();
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

            abort(422, 'Invalid state');
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
