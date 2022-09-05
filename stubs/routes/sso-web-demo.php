<?php

use Illuminate\Support\Facades\Route;

// This is just a demo! Feel free to remove!
Route::get('/sso-web-demo', 'SSO\Web\DemoController@index')->middleware('imissu-web');

// Role for admin only can access
Route::get('/sso-web-demo/admin', 'SSO\Web\DemoController@admin')->middleware('imissu-web-role:Admin');