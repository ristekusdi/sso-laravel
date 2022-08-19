<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeDemoController extends Controller
{
    public function index()
    {
        return view('home-demo');
    }

    public function admin()
    {
        return 'Hello admin!';
    }
}
