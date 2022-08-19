<?php

use Illuminate\Support\Facades\Route;

Route::post('/web-session/change-role-active', [\App\Http\Controllers\SSO\Web\WebSessionController::class, 'changeRoleActive'])->middleware('imissu-web');