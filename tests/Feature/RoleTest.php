<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create();
});

it('renders the roles index for users with manage roles permission', function () {
    Permission::findOrCreate('manage roles');
    $this->user->givePermissionTo('manage roles');

    $this->actingAs($this->user)
        ->get(route('alumkit.roles.index'))
        ->assertOk();
});

it('denies access to roles index for users without manage roles permission', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.roles.index'))
        ->assertForbidden();
});

it('renders the create role form for users with manage roles permission', function () {
    Permission::findOrCreate('manage roles');
    $this->user->givePermissionTo('manage roles');

    $this->actingAs($this->user)
        ->get(route('alumkit.roles.create'))
        ->assertOk();
});

it('creates a role with permissions', function () {
    Permission::findOrCreate('manage roles');
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage roles');

    $this->actingAs($this->user)
        ->post(route('alumkit.roles.store'), [
            'name' => 'editor',
            'permissions' => ['manage members'],
        ])
        ->assertRedirect(route('alumkit.roles.index'));

    $this->assertDatabaseHas('roles', ['name' => 'editor']);
    $this->assertDatabaseHas('role_has_permissions', [
        'role_id' => Role::where('name', 'editor')->first()->id,
        'permission_id' => Permission::where('name', 'manage members')->first()->id,
    ]);
});

it('renders the edit role form for users with manage roles permission', function () {
    Permission::findOrCreate('manage roles');
    $this->user->givePermissionTo('manage roles');

    $role = Role::findOrCreate('test-role');

    $this->actingAs($this->user)
        ->get(route('alumkit.roles.edit', $role))
        ->assertOk();
});

it('updates a role and syncs permissions', function () {
    Permission::findOrCreate('manage roles');
    Permission::findOrCreate('manage members');
    Permission::findOrCreate('view dashboard');
    $this->user->givePermissionTo('manage roles');

    $role = Role::findOrCreate('test-role');
    $role->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.roles.update', $role), [
            'name' => 'updated-role',
            'permissions' => ['view dashboard'],
        ])
        ->assertRedirect(route('alumkit.roles.index'));

    $this->assertDatabaseHas('roles', ['name' => 'updated-role']);
    expect($role->fresh()->permissions->pluck('name')->toArray())->toBe(['view dashboard']);
});

it('deletes a role', function () {
    Permission::findOrCreate('manage roles');
    $this->user->givePermissionTo('manage roles');

    $role = Role::findOrCreate('test-role');

    $this->actingAs($this->user)
        ->delete(route('alumkit.roles.destroy', $role))
        ->assertRedirect(route('alumkit.roles.index'));

    $this->assertDatabaseMissing('roles', ['name' => 'test-role']);
});
