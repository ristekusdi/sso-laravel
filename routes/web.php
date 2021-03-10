<?php

use Illuminate\Support\Facades\Route;
use RistekUSDI\SSO\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'login'])->name('sso.login');
Route::get('/logout', [AuthController::class, 'logout'])->name('sso.logout');
Route::get('/callback', [AuthController::class, 'callback'])->name('sso.callback');