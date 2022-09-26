<?php

namespace App\Http\Controllers\SSO\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdvanceController extends Controller
{
    public function index()
    {
        return view('sso-web.advance');
    }

    public function admin()
    {
        return 'Hello admin!';
    }
}
