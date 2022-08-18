<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

Route::post('/login', [AuthController::class, 'login'])->name('sso.token.login');
Route::get('/userinfo', [AuthController::class, 'userinfo'])
    ->middleware('imissu-token')
    ->name('sso.token.userinfo');