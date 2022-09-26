<?php

namespace App\Http\Controllers\SSO\Web;

use App\Http\Controllers\Controller;

class BasicController extends Controller
{
    public function index()
    {
        return view('sso-web.basic');
    }
}
