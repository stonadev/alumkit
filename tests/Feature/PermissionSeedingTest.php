<?php

declare(strict_types=1);

use Alumkit\Alumkit\Database\Seeders\AlumkitRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('creates the expected permissions', function () {
    $this->seed(AlumkitRolesAndPermissionsSeeder::class);

    $permissions = Permission::pluck('name')->toArray();

    expect($permissions)->toContain('manage roles');
    expect($permissions)->toContain('manage permissions');
    expect($permissions)->toContain('manage members');
    expect($permissions)->toContain('view dashboard');
    expect($permissions)->toHaveCount(4);
});

it('creates the expected roles', function () {
    $this->seed(AlumkitRolesAndPermissionsSeeder::class);

    $roles = Role::pluck('name')->toArray();

    expect($roles)->toContain('admin');
    expect($roles)->toContain('moderator');
    expect($roles)->toContain('member');
    expect($roles)->toHaveCount(3);
});

it('assigns all permissions to the admin role', function () {
    $this->seed(AlumkitRolesAndPermissionsSeeder::class);

    $adminRole = Role::findByName('admin');

    expect($adminRole->permissions->count())->toBe(4);
    expect($adminRole->permissions->pluck('name')->toArray())->toBe([
        'manage roles',
        'manage permissions',
        'manage members',
        'view dashboard',
    ]);
});

it('assigns correct permissions to the moderator role', function () {
    $this->seed(AlumkitRolesAndPermissionsSeeder::class);

    $moderatorRole = Role::findByName('moderator');

    expect($moderatorRole->permissions->count())->toBe(2);
    expect($moderatorRole->permissions->pluck('name')->toArray())->toBe([
        'manage members',
        'view dashboard',
    ]);
});

it('assigns no permissions to the member role', function () {
    $this->seed(AlumkitRolesAndPermissionsSeeder::class);

    $memberRole = Role::findByName('member');

    expect($memberRole->permissions->count())->toBe(0);
});
