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

    public function basic()
    {
        return view('sso-web.basic');
    }

    public function advance()
    {
        return view('sso-web.advance');
    }

    public function admin()
    {
        return 'Admin page';
    }
}