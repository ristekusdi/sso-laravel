<?php

use App\Http\Controllers\SSO\Web\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('imissu-web')->group(function () {
    Route::post('/web-session/change-role', [SessionController::class, 'changeRole']);
    Route::post('/web-session/change-kv', [SessionController::class, 'changeKeyValue']);
});