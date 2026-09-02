<?php

declare(strict_types=1);

use Alumkit\Alumkit\Http\Controllers\ActivityLogController;
use Alumkit\Alumkit\Http\Controllers\AssetController;
use Alumkit\Alumkit\Http\Controllers\CareerController;
use Alumkit\Alumkit\Http\Controllers\CommitteeController;
use Alumkit\Alumkit\Http\Controllers\CompleteProfileController;
use Alumkit\Alumkit\Http\Controllers\EditorImageController;
use Alumkit\Alumkit\Http\Controllers\EducationController;
use Alumkit\Alumkit\Http\Controllers\GlobalContentController;
use Alumkit\Alumkit\Http\Controllers\MemberController;
use Alumkit\Alumkit\Http\Controllers\PageController;
use Alumkit\Alumkit\Http\Controllers\PositionController;
use Alumkit\Alumkit\Http\Controllers\PostController;
use Alumkit\Alumkit\Http\Controllers\ProfileCareerController;
use Alumkit\Alumkit\Http\Controllers\ProfileDetailsController;
use Alumkit\Alumkit\Http\Controllers\ProfileEducationController;
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

// Committee photos: streamed through the package (no storage:link requirement).
Route::get('alumkit/style/committee-photos/{file}', function (string $file) {
    $path = 'committee-photos/'.basename($file);

    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->name('alumkit.committee.photo')->where('file', '[\w.\-]+');

Route::middleware(['web'])->group(function () {
    // Profile completion: accessible after email verification, before full profile is submitted.
    Route::middleware(['auth', 'verified', 'user.suspended'])->group(function () {
        Route::get('profile/complete', [CompleteProfileController::class, 'create'])->name('alumkit.profile.complete');
        Route::post('profile/complete', [CompleteProfileController::class, 'store'])->name('alumkit.profile.complete.store');
        Route::post('alumkit/editor/image', [EditorImageController::class, 'store'])->name('alumkit.editor.image');
    });

    // Protected routes: require auth, email verification, and completed profile.
    // Suspended users keep /dashboard access; sub-routes redirect back via user.suspended.
    Route::middleware(['auth', 'verified', 'complete-profile.check'])->group(function () {
        Route::get('dashboard', function () {
            /** @phpstan-ignore argument.type */
            return view('alumkit::dashboard');
        })->name('alumkit.dashboard');

        Route::prefix('dashboard')->name('alumkit.')->middleware('user.suspended')->group(function () {
            Route::middleware('permission:manage members')->group(function () {
                Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity.index');
            });

            Route::get('profile', function () {
                /** @phpstan-ignore argument.type */
                return view('alumkit::profile.show');
            })->name('profile');

            Route::put('profile/details', [ProfileDetailsController::class, 'update'])->name('profile.details.update');

            Route::prefix('profile')->name('profile.')->whereNumber(['education', 'career'])->group(function () {
                Route::get('educations/create', [ProfileEducationController::class, 'create'])->name('educations.create');
                Route::post('educations', [ProfileEducationController::class, 'store'])->name('educations.store');
                Route::get('educations/{education}/edit', [ProfileEducationController::class, 'edit'])->name('educations.edit');
                Route::put('educations/{education}', [ProfileEducationController::class, 'update'])->name('educations.update');
                Route::delete('educations/{education}', [ProfileEducationController::class, 'destroy'])->name('educations.destroy');

                Route::get('careers/create', [ProfileCareerController::class, 'create'])->name('careers.create');
                Route::post('careers', [ProfileCareerController::class, 'store'])->name('careers.store');
                Route::get('careers/{career}/edit', [ProfileCareerController::class, 'edit'])->name('careers.edit');
                Route::put('careers/{career}', [ProfileCareerController::class, 'update'])->name('careers.update');
                Route::delete('careers/{career}', [ProfileCareerController::class, 'destroy'])->name('careers.destroy');
            });
            Route::middleware('permission:manage roles')->group(function () {
                Route::resource('roles', RoleController::class)->except(['show']);
            });

            Route::middleware('permission:manage educations')->group(function () {
                Route::resource('educations', EducationController::class)->except(['show']);
            });

            Route::middleware('permission:manage careers')->group(function () {
                Route::resource('careers', CareerController::class)->except(['show']);
            });
            Route::middleware('permission:manage pages')->group(function () {
                Route::resource('pages', PageController::class)->only(['index', 'edit', 'update']);

                Route::get('globals', [GlobalContentController::class, 'index'])->name('globals.index');
                Route::get('globals/{key}', [GlobalContentController::class, 'edit'])->name('globals.edit');
                Route::put('globals/{key}', [GlobalContentController::class, 'update'])->name('globals.update');
            });

            Route::middleware('user.approved')->group(function () {
                Route::resource('posts', PostController::class)->except(['show']);
            });

            Route::middleware('user.approved')->group(function () {
                Route::get('members', [MemberController::class, 'index'])->name('members.index');
                Route::get('members/{user}', [MemberController::class, 'show'])->name('members.show');
            });

            Route::middleware('permission:manage members')->group(function () {
                Route::get('users', [UserRoleController::class, 'index'])->name('users.index');
                Route::get('users/{user}', [UserRoleController::class, 'show'])->name('users.show');
                Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
                Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
                Route::put('users/{user}/state', [UserStateController::class, 'update'])->name('users.state.update');
            });

            Route::middleware('permission:manage committee')->group(function () {
                Route::resource('positions', PositionController::class)->except(['show']);
                Route::resource('committee', CommitteeController::class)->except(['show']);
                Route::post('committee/reorder', [CommitteeController::class, 'reorder'])->name('committee.reorder');
            });
        });
    });
});
