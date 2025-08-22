<?php

namespace App\Http\Controllers\SSO\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function changeRole(Request $request) 
    {   
        auth('imissu-web')->user()->role = json_decode($request->role); 
        return response()->json([ 
            'message' => 'Berhasil mengubah peran', 
        ], 204); 
    }

    public function changeKeyValue(Request $request)
    {
        auth('imissu-web')->user()->{$request->key} = $request->value;
        return response()->json([ 
            'message' => 'Berhasil mengubah key and value', 
        ], 204); 
    }
}