<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['imissu-web'])->group(function () {
    Route::view('/sso-web-demo', 'sso-web.demo');
});
