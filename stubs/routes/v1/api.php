<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', 'SSO\Token\AuthController@login')->name('sso.token.login');
Route::get('/userinfo', 'SSO\Token\AuthController@userinfo')->middleware('imissu-token')->name('sso.token.userinfo');