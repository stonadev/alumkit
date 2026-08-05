<?php

declare(strict_types=1);

use Alumkit\Alumkit\Http\Controllers\CompleteProfileController;
use Alumkit\Alumkit\Http\Controllers\EducationController;
use Alumkit\Alumkit\Http\Controllers\RoleController;
use Alumkit\Alumkit\Http\Controllers\UserRoleController;
use Alumkit\Alumkit\Http\Controllers\UserStateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    // Profile completion: accessible after email verification, before full profile is submitted.
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('profile/complete', [CompleteProfileController::class, 'create'])->name('alumkit.profile.complete');
        Route::post('profile/complete', [CompleteProfileController::class, 'store'])->name('alumkit.profile.complete.store');
    });

    // Protected routes: require auth, email verification, active/pending state, and completed profile.
    Route::middleware(['auth', 'verified', 'user.state', 'complete-profile.check'])->group(function () {
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

            Route::middleware('permission:manage educations')->group(function () {
                Route::resource('educations', EducationController::class)->except(['show']);
            });

            Route::middleware('permission:manage members')->group(function () {
                Route::get('users', [UserRoleController::class, 'index'])->name('users.index');
                Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
                Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
                Route::put('users/{user}/state', [UserStateController::class, 'update'])->name('users.state.update');
            });
        });
    });
});
