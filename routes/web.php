<?php

use Illuminate\Support\Facades\Route;
use RistekUSDI\SSO\Http\Controllers\AuthController;

Route::get('/sso/login', [AuthController::class, 'login'])->name('sso.login');
Route::get('/sso/logout', [AuthController::class, 'logout'])->name('sso.logout');
Route::get('/sso/callback', [AuthController::class, 'callback'])->name('sso.callback');