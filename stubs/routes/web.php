<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SSO\Web\AuthController;

Route::get('/sso/login', [AuthController::class, 'login'])->name('sso.web.login');
Route::get('/sso/logout', [AuthController::class, 'logout'])->name('sso.web.logout');
Route::get('/sso/callback', [AuthController::class, 'callback'])->name('sso.web.callback');