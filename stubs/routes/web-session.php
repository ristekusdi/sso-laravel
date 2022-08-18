<?php

use Illuminate\Support\Facades\Route;

Route::post('/web-session/change-role-active', [\App\Http\Controllers\WebSessionController::class, 'changeRoleActive'])->middleware('imissu-web');