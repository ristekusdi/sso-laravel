<?php

use Illuminate\Support\Facades\Route;

// This is just a demo! Feel free to remove!
Route::get('/home-demo', [\App\Http\Controllers\HomeDemoController::class, 'index'])
    ->middleware('imissu-web');

// Role for admin only can access
Route::get('/home-demo/admin', [\App\Http\Controllers\HomeDemoController::class, 'admin'])
    ->middleware('imissu-web-role:Admin');