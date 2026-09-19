<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->member = User::factory()->approved()->withProfile()->create(['name' => 'Active Member']);
    $this->member->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);

    $this->pending = User::factory()->withProfile()->create(['name' => 'Pending Member']);
});

it('lists active members for active users', function () {
    $this->actingAs($this->member)
        ->get(route('alumkit.users.index'))
        ->assertOk()
        ->assertSee('Members')
        ->assertSee('Active Member')
        ->assertSee('MIT')
        ->assertDontSee('Pending Member');
});

it('shows a member profile to an active user', function () {
    $this->actingAs($this->member)
        ->get(route('alumkit.users.show', $this->member))
        ->assertOk()
        ->assertSee('MIT')
        ->assertSee($this->member->email);
});

it('denies the directory to pending users', function () {
    $this->actingAs($this->pending)
        ->get(route('alumkit.users.index'))
        ->assertForbidden();
});

it('does not expose non-active profiles', function () {
    $this->actingAs($this->member)
        ->get(route('alumkit.users.show', $this->pending))
        ->assertNotFound();
});

it('omits admin actions from the member profile view', function () {
    $this->actingAs($this->member)
        ->get(route('alumkit.users.show', $this->member))
        ->assertOk()
        ->assertDontSee(__('alumkit::dashboard.transition_to_active'))
        ->assertDontSee(__('alumkit::dashboard.assign_roles'));
});

it('links the directory in the sidebar for active members', function () {
    $this->actingAs($this->member)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee(route('alumkit.users.index'));
});

it('keeps the management list in the sidebar for admins', function () {
    $admin = User::factory()->approved()->withProfile()->create(['name' => 'Admin Member']);
    Permission::findOrCreate('manage members');
    $admin->givePermissionTo('manage members');

    $this->actingAs($admin)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee(route('alumkit.users.index'));
});
