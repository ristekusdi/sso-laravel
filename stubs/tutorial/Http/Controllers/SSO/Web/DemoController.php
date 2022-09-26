<?php

namespace App\Http\Controllers\SSO\Web;

use App\Http\Controllers\Controller;

class DemoController extends Controller
{
    public function index()
    {
        return view('sso-web.demo');
    }

    public function admin()
    {
        return 'Hello admin!';
    }
}
