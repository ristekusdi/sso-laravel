<?php

use Illuminate\Support\Facades\Route;

Route::get('/sso/login', 'SSO\Web\AuthController@login')->name('sso.web.login');
Route::get('/sso/logout', 'SSO\Web\AuthController@logout')->name('sso.web.logout');
Route::get('/sso/callback', 'SSO\Web\AuthController@callback')->name('sso.web.callback');