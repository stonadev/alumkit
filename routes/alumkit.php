<?php

declare(strict_types=1);

use Alumkit\Alumkit\Http\Controllers\AssetController;
use Alumkit\Alumkit\Http\Controllers\CareerController;
use Alumkit\Alumkit\Http\Controllers\CompleteProfileController;
use Alumkit\Alumkit\Http\Controllers\EditorImageController;
use Alumkit\Alumkit\Http\Controllers\EducationController;
use Alumkit\Alumkit\Http\Controllers\PostController;
use Alumkit\Alumkit\Http\Controllers\ProfileDetailsController;
use Alumkit\Alumkit\Http\Controllers\RoleController;
use Alumkit\Alumkit\Http\Controllers\UserRoleController;
use Alumkit\Alumkit\Http\Controllers\UserStateController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Package assets: compiled CSS/JS served straight from vendor, so
// `composer update stonadev/alumkit` alone ships new styles and scripts.
Route::get('alumkit/style/{file}', AssetController::class)
    ->where('file', '[A-Za-z0-9._-]+');

// Editor image uploads: streamed through the package (no storage:link requirement).
Route::get('alumkit/style/editor-images/{file}', function (string $file) {
    $path = 'editor-images/'.basename($file);

    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->name('alumkit.editor.image.show')->where('file', '[\w.\-]+');

// Post thumbnails: streamed through the package (no storage:link requirement).
Route::get('alumkit/style/post-thumbnails/{file}', function (string $file) {
    $path = 'post-thumbnails/'.basename($file);

    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->name('alumkit.posts.thumbnail')->where('file', '[\w.\-]+');

// Profile photos: streamed through the package (no storage:link requirement).
Route::get('alumkit/style/profile-photos/{file}', function (string $file) {
    $path = 'profile-photos/'.basename($file);

    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->name('alumkit.profile.photo.show')->where('file', '[\w.\-]+');

Route::middleware(['web'])->group(function () {
    // Profile completion: accessible after email verification, before full profile is submitted.
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('profile/complete', [CompleteProfileController::class, 'create'])->name('alumkit.profile.complete');
        Route::post('profile/complete', [CompleteProfileController::class, 'store'])->name('alumkit.profile.complete.store');
        Route::post('alumkit/editor/image', [EditorImageController::class, 'store'])->name('alumkit.editor.image');
    });

    // Protected routes: require auth, email verification, active/pending state, and completed profile.
    Route::middleware(['auth', 'verified', 'user.state', 'complete-profile.check'])->group(function () {
        Route::get('dashboard', function () {
            /** @phpstan-ignore argument.type */
            return view('alumkit::dashboard');
        })->name('alumkit.dashboard');

        Route::get('profile', function () {
            /** @phpstan-ignore argument.type */
            return view('alumkit::profile.show');
        })->name('alumkit.profile');

        Route::put('profile/details', [ProfileDetailsController::class, 'update'])->name('alumkit.profile.details.update');

        Route::prefix('dashboard')->name('alumkit.')->group(function () {
            Route::middleware('permission:manage roles')->group(function () {
                Route::resource('roles', RoleController::class)->except(['show']);
            });

            Route::middleware('permission:manage educations')->group(function () {
                Route::resource('educations', EducationController::class)->except(['show']);
            });

            Route::middleware('permission:manage careers')->group(function () {
                Route::resource('careers', CareerController::class)->except(['show']);
            });

            Route::middleware('user.approved')->group(function () {
                Route::resource('posts', PostController::class)->except(['show']);
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
