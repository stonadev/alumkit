<?php

declare(strict_types=1);

use Alumkit\Alumkit\Http\Controllers\RoleController;
use Alumkit\Alumkit\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('dashboard', function () {
            /** @phpstan-ignore argument.type */
            return view('alumkit::layouts.dashboard');
        })->name('alumkit.dashboard');

        Route::get('profile', function () {
            /** @phpstan-ignore argument.type */
            return view('alumkit::profile.show');
        })->name('alumkit.profile');

        Route::prefix('dashboard')->name('alumkit.')->group(function () {
            Route::middleware('permission:manage roles')->group(function () {
                Route::resource('roles', RoleController::class)->except(['show']);
            });

            Route::middleware('permission:manage members')->group(function () {
                Route::get('users', [UserRoleController::class, 'index'])->name('users.index');
                Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
                Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
            });
        });
    });
});
