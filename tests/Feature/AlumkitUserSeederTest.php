<?php

declare(strict_types=1);

use Alumkit\Alumkit\Database\Seeders\AlumkitRolesAndPermissionsSeeder;
use Alumkit\Alumkit\Database\Seeders\AlumkitUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('seeds the admin user from config values', function () {
    config()->set('alumkit.seeder.admin_name', 'Custom Admin');
    config()->set('alumkit.seeder.admin_email', 'custom@example.com');
    config()->set('alumkit.seeder.admin_password', 'secret123');

    $this->seed([AlumkitRolesAndPermissionsSeeder::class, AlumkitUserSeeder::class]);

    $user = User::where('email', 'custom@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Custom Admin');
    expect(Hash::check('secret123', $user->password))->toBeTrue();
    expect($user->hasRole('admin'))->toBeTrue();
});

it('does not duplicate the admin user on re-seed', function () {
    $this->seed([AlumkitRolesAndPermissionsSeeder::class, AlumkitUserSeeder::class]);
    $this->seed([AlumkitRolesAndPermissionsSeeder::class, AlumkitUserSeeder::class]);

    expect(User::where('email', 'admin@example.com')->count())->toBe(1);
});

it('verifies an existing unverified admin on re-seed', function () {
    User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->seed([AlumkitRolesAndPermissionsSeeder::class, AlumkitUserSeeder::class]);

    expect(User::where('email', 'admin@example.com')->first()->email_verified_at)->not->toBeNull();
});

it('uses default admin credentials when unconfigured', function () {
    $this->seed([AlumkitRolesAndPermissionsSeeder::class, AlumkitUserSeeder::class]);

    expect(User::where('email', 'admin@example.com')->exists())->toBeTrue();
});

it('skips seeding when the configured user model does not exist', function () {
    config()->set('alumkit.auth.user_model', 'App\\Models\\DoesNotExist');

    $this->seed(AlumkitUserSeeder::class);

    expect(User::count())->toBe(0);
});
