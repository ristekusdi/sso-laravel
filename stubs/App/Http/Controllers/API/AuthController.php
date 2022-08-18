<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\API\CredentialRequest;

class AuthController extends Controller
{
    public function getBaseUrl()
    {
        return config('sso.base_url');
    }

    public function getRealm()
    {
        return config('sso.realm');
    }

    public function login(CredentialRequest $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->getBaseUrl()}/realms/{$this->getRealm()}/protocol/openid-connect/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($request->toArray()),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
            )
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);

        return response()->json(json_decode($response, true), $httpcode);
    }

    public function userinfo()
    {
        return response()->json(auth('imissu-token')->user()->getAttributes());
    }
}
