<?php

use Illuminate\Support\Facades\Route;
use Leeto\MoonShine\Http\Controllers\AuthController;
use Leeto\MoonShine\Http\Controllers\DashboardController;
use Leeto\MoonShine\Http\Controllers\InitialController;

Route::prefix(config('moonshine.prefix'))
    ->middleware(['moonshine'])
    ->name(config('moonshine.prefix').'.')->group(function () {
        Route::get('/initial', InitialController::class)->name('initial');

        Route::controller(AuthController::class)->group(function () {
            Route::post('/authenticate', 'authenticate')->name('authenticate');
            Route::delete('/logout', 'logout')->name('logout');
            Route::get('/authenticate', 'me')
                ->middleware(['auth:sanctum'])
                ->name('authenticate.me');
        });

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/', DashboardController::class)->name('dashboard');
        });
    });
