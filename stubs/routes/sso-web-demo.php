<?php

use Illuminate\Support\Facades\Route;

// This is just a demo! Feel free to remove!
Route::get('/sso-web-demo', [\App\Http\Controllers\SSO\Web\DemoController::class, 'index'])
    ->middleware('imissu-web');

// Role for admin only can access
Route::get('/sso-web-demo/admin', [\App\Http\Controllers\SSO\Web\DemoController::class, 'admin'])
    ->middleware('imissu-web-role:Admin');