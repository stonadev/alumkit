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

it('publishes all Alumkit resources', function () {
    $path = config_path('permission.php');

    try {
        @unlink($path); // idempotent: no stale file from prior runs

        $this->artisan('alumkit:publish')->assertSuccessful();

        expect(file_exists($path))->toBeTrue();
        expect((array) include $path)->toHaveKey('alumkit'); // proves it's OUR permission config
    } finally {
        @unlink($path); // leave the testbench skeleton clean
    }
});

it('skips existing files and overwrites with --force', function () {
    $path = config_path('permission.php');

    try {
        @unlink($path);

        $this->artisan('alumkit:publish')->assertSuccessful();
        file_put_contents($path, "<?php\n\nreturn ['stale' => true];\n");

        // default: skip-existing, stale file survives
        $this->artisan('alumkit:publish')->assertSuccessful();
        expect((array) include $path)->toEqual(['stale' => true]);

        // --force: package copy restored
        $this->artisan('alumkit:publish', ['--force' => true])->assertSuccessful();
        expect((array) include $path)->toHaveKey('alumkit');
    } finally {
        @unlink($path); // leave the testbench skeleton clean
    }
});
