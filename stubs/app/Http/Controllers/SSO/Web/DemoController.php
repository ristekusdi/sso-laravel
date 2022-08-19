<?php

namespace App\Http\Controllers\SSO\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
