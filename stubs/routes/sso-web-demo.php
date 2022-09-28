<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['imissu-web'])->group(function () {
    Route::get('/sso-web-demo', [App\Http\Controllers\SSO\Web\DemoController::class, 'index']);
    Route::get('/sso-web-demo/basic', [App\Http\Controllers\SSO\Web\DemoController::class, 'basic']);
    Route::get('/sso-web-demo/advance', [App\Http\Controllers\SSO\Web\DemoController::class, 'advance']);
    Route::post('/sso-web-demo/change-role-active', [App\Http\Controllers\SSO\Web\DemoController::class, 'changeRoleActive']);
    // Only user that have role active Admin can access!
    Route::get('/sso-web-demo/admin', [\App\Http\Controllers\SSO\Web\DemoController::class, 'admin'])->middleware('imissu-web.role_active:Admin');
});
