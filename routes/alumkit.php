<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('dashboard', function () {
            /** @phpstan-ignore argument.type */
            return view('alumkit::auth.dashboard');
        })->name('alumkit.dashboard');

        Route::get('profile', function () {
            /** @phpstan-ignore argument.type */
            return view('alumkit::profile.show');
        })->name('alumkit.profile');
    });
});
