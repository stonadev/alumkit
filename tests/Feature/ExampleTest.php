<?php

declare(strict_types=1);

use Alumkit\Alumkit\Alumkit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('resolves the singleton', function () {
    expect(app(Alumkit::class))->toBeInstanceOf(Alumkit::class);
});

it('returns the same instance from the container', function () {
    expect(app(Alumkit::class))->toBe(app(Alumkit::class));
});

it('merges the package config', function () {
    expect(config('alumkit.auth.user_model'))->toBeString()->not->toBeEmpty();
});

it('loads the package translations', function () {
    expect(trans('alumkit::messages.placeholder'))->toBe('Alumkit placeholder translation.');
});

it('loads the package views', function () {
    expect(view()->exists('alumkit::placeholder'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('alumkit:seed')
        ->expectsOutputToContain('Alumkit roles, permissions, and admin user seeded.')
        ->assertSuccessful();

    expect(Role::pluck('name'))->toContain('admin', 'moderator', 'member');
    expect(Permission::count())->toBeGreaterThan(0);
    expect(User::where('email', 'admin@example.com')->exists())->toBeTrue();
});
