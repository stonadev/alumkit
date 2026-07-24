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
    $this->targetUser = User::factory()->create();
});

it('renders the user roles edit form for users with manage members permission', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->get(route('alumkit.users.roles.edit', $this->targetUser))
        ->assertOk();
});

it('denies access to user roles edit form for users without manage members permission', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.users.roles.edit', $this->targetUser))
        ->assertForbidden();
});

it('assigns roles to a user', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    Role::findOrCreate('admin');
    Role::findOrCreate('member');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.roles.update', $this->targetUser), [
            'roles' => ['admin', 'member'],
        ])
        ->assertRedirect(route('alumkit.users.roles.edit', $this->targetUser));

    expect($this->targetUser->fresh()->roles->pluck('name')->toArray())->toBe(['admin', 'member']);
});

it('removes roles from a user', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    Role::findOrCreate('admin');
    Role::findOrCreate('member');

    $this->targetUser->syncRoles(['admin', 'member']);

    $this->actingAs($this->user)
        ->put(route('alumkit.users.roles.update', $this->targetUser), [
            'roles' => ['admin'],
        ])
        ->assertRedirect(route('alumkit.users.roles.edit', $this->targetUser));

    expect($this->targetUser->fresh()->roles->pluck('name')->toArray())->toBe(['admin']);
});
