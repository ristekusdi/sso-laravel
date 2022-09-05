<?php

use Illuminate\Support\Facades\Route;

Route::post('/web-session/change-role-active', 'SSO\Web\WebSessionController@changeRoleActive')->middleware('imissu-web');