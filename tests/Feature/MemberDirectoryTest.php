<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->member = User::factory()->approved()->create(['name' => 'Active Member']);
    $this->member->profile()->create();
    $this->member->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);

    $this->pending = User::factory()->create(['name' => 'Pending Member']);
    $this->pending->profile()->create();
});

it('lists active members for active users', function () {
    $this->actingAs($this->member)
        ->get(route('alumkit.members.index'))
        ->assertOk()
        ->assertSee('Active Member')
        ->assertDontSee('Pending Member');
});

it('shows a member profile to an active user', function () {
    $this->actingAs($this->member)
        ->get(route('alumkit.members.show', $this->member))
        ->assertOk()
        ->assertSee($this->member->email)
        ->assertSee('MIT');
});

it('denies the directory to pending users', function () {
    $this->actingAs($this->pending)
        ->get(route('alumkit.members.index'))
        ->assertForbidden();
});

it('does not expose non-active profiles', function () {
    $this->actingAs($this->member)
        ->get(route('alumkit.members.show', $this->pending))
        ->assertNotFound();
});

it('omits admin actions from the member profile view', function () {
    $this->actingAs($this->member)
        ->get(route('alumkit.members.show', $this->member))
        ->assertOk()
        ->assertDontSee('transition_to_active')
        ->assertDontSee('assign_roles');
});

it('links the directory in the sidebar for active members', function () {
    $this->actingAs($this->member)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee(route('alumkit.members.index'));
});

it('keeps the management list in the sidebar for admins', function () {
    $admin = User::factory()->approved()->create(['name' => 'Admin Member']);
    $admin->profile()->create();
    Permission::findOrCreate('manage members');
    $admin->givePermissionTo('manage members');

    $this->actingAs($admin)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee(route('alumkit.users.index'));
});
