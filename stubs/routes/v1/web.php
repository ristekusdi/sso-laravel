<?php

use Illuminate\Support\Facades\Route;

Route::get('/sso/login', 'App\Http\Controllers\SSO\Web\AuthController@login')->name('sso.web.login');
Route::get('/sso/logout', 'App\Http\Controllers\SSO\Web\AuthController@logout')->name('sso.web.logout');
Route::get('/sso/callback', 'App\Http\Controllers\SSO\Web\AuthController@callback')->name('sso.web.callback');