<?php

use Illuminate\Support\Facades\Route;

Route::get('/sso/login', 'RistekUSDI\SSO\Http\Controllers\AuthController@login')->name('sso.login');
Route::get('/sso/logout', 'RistekUSDI\SSO\Http\Controllers\AuthController@logout')->name('sso.logout');
Route::get('/sso/callback', 'RistekUSDI\SSO\Http\Controllers\AuthController@callback')->name('sso.callback');