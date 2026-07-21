<?php

declare(strict_types=1);

use Alumkit\Alumkit\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('login', [AuthController::class, 'login'])
        ->name('alumkit.login');

    Route::post('logout', [AuthController::class, 'logout'])
        ->name('alumkit.logout');

    Route::middleware('auth')->group(function () {
        Route::get('dashboard', [AuthController::class, 'dashboard'])
            ->name('alumkit.dashboard');
    });
});
