<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', 'App\Http\Controllers\SSO\Token\AuthController@login')->name('sso.token.login');
Route::get('/userinfo', 'App\Http\Controllers\SSO\Token\AuthController@userinfo')->middleware('imissu-token')->name('sso.token.userinfo');