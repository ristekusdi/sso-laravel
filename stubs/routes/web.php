<?php

use Illuminate\Support\Facades\Route;

Route::get('/sso/login', 'SSO\AuthController@login')->name('sso.login');
Route::get('/sso/logout', 'SSO\AuthController@logout')->name('sso.logout');
Route::get('/sso/callback', 'SSO\AuthController@callback')->name('sso.callback');