<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->profile()->create();
    Permission::findOrCreate('manage members');
    $this->admin->givePermissionTo('manage members');

    $this->pendingUser = User::factory()->create(['name' => 'Pending Member']);
    $this->pendingUser->profile()->create();
    $this->pendingUser->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);
    $this->pendingUser->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);

    $this->activeUser = User::factory()->approved()->create(['name' => 'Active Member']);
});

it('defaults the users index to pending users', function () {
    $this->actingAs($this->admin)
        ->get(route('alumkit.users.index'))
        ->assertOk()
        ->assertSee('Pending Member')
        ->assertDontSee('Active Member');
});

it('shows all users with the all filter', function () {
    $this->actingAs($this->admin)
        ->get(route('alumkit.users.index', ['filter' => 'all']))
        ->assertOk()
        ->assertSee('Pending Member')
        ->assertSee('Active Member');
});

it('filters users by rejected state', function () {
    $rejected = User::factory()->create(['name' => 'Rejected Member', 'state' => 'rejected']);
    $rejected->profile()->create();

    $this->actingAs($this->admin)
        ->get(route('alumkit.users.index', ['filter' => 'rejected']))
        ->assertOk()
        ->assertSee('Rejected Member')
        ->assertDontSee('Pending Member')
        ->assertDontSee('Active Member');
});

it('filters users by suspended state', function () {
    $suspended = User::factory()->create(['name' => 'Suspended Member', 'state' => 'suspended']);
    $suspended->profile()->create();

    $this->actingAs($this->admin)
        ->get(route('alumkit.users.index', ['filter' => 'suspended']))
        ->assertOk()
        ->assertSee('Suspended Member')
        ->assertDontSee('Pending Member')
        ->assertDontSee('Active Member');
});

it('defaults to the all filter when no pending users exist', function () {
    // This test deletes every pending user; the acting admin must not be one,
    // or the request runs as a deleted user (FK cascade removes the profile).
    $this->admin->update(['state' => 'active']);

    User::query()->where('state', 'pending')->delete();

    $this->actingAs($this->admin)
        ->get(route('alumkit.users.index'))
        ->assertOk()
        ->assertSee('Active Member');
});

it('falls back to pending for unknown filter values', function () {
    $this->actingAs($this->admin)
        ->get(route('alumkit.users.index', ['filter' => 'bogus']))
        ->assertOk()
        ->assertDontSee('Active Member');
});

it('renders user details for users with manage members permission', function () {
    $this->actingAs($this->admin)
        ->get(route('alumkit.users.show', $this->pendingUser))
        ->assertOk()
        ->assertSee($this->pendingUser->email)
        ->assertSee('MIT')
        ->assertSee('Developer');
});

it('denies user details without manage members permission', function () {
    $deniedUser = User::factory()->create();
    $deniedUser->profile()->create();

    $this->actingAs($deniedUser)
        ->get(route('alumkit.users.show', $this->pendingUser))
        ->assertForbidden();
});

it('renders review actions for a pending member', function () {
    $this->actingAs($this->admin)
        ->get(route('alumkit.users.show', $this->pendingUser))
        ->assertOk()
        ->assertSee(__('alumkit::dashboard.transition_to_active'))
        ->assertSee(__('alumkit::dashboard.transition_to_rejected'));
});

it('renders the suspend action for an active member', function () {
    $this->actingAs($this->admin)
        ->get(route('alumkit.users.show', $this->activeUser))
        ->assertOk()
        ->assertSee(__('alumkit::dashboard.transition_to_suspended'));
});

it('renders the resubmit action for a rejected member', function () {
    $rejected = User::factory()->create(['name' => 'Rejected Member', 'state' => 'rejected']);
    $rejected->profile()->create();

    $this->actingAs($this->admin)
        ->get(route('alumkit.users.show', $rejected))
        ->assertOk()
        ->assertSee(__('alumkit::dashboard.transition_to_pending'));
});

it('approves a pending member', function () {
    $this->actingAs($this->admin)
        ->put(route('alumkit.users.state.update', $this->pendingUser), ['state' => 'active'])
        ->assertRedirect(route('alumkit.users.index'))
        ->assertSessionHas('status');

    expect($this->pendingUser->fresh()->state)->toBe('active');
});

it('rejects a pending member', function () {
    $this->actingAs($this->admin)
        ->put(route('alumkit.users.state.update', $this->pendingUser), ['state' => 'rejected'])
        ->assertRedirect(route('alumkit.users.index'));

    expect($this->pendingUser->fresh()->state)->toBe('rejected');
});

it('moves a rejected member back to the review queue', function () {
    $rejected = User::factory()->create(['name' => 'Rejected Member', 'state' => 'rejected']);
    $rejected->profile()->create();

    $this->actingAs($this->admin)
        ->put(route('alumkit.users.state.update', $rejected), ['state' => 'pending'])
        ->assertRedirect(route('alumkit.users.index'));

    expect($rejected->fresh()->state)->toBe('pending');
});

it('suspends an active member', function () {
    $this->actingAs($this->admin)
        ->put(route('alumkit.users.state.update', $this->activeUser), ['state' => 'suspended'])
        ->assertRedirect(route('alumkit.users.index'));

    expect($this->activeUser->fresh()->state)->toBe('suspended');
});

it('blocks an admin from changing their own state', function () {
    $this->actingAs($this->admin)
        ->put(route('alumkit.users.state.update', $this->admin), ['state' => 'active'])
        ->assertRedirect(route('alumkit.users.show', $this->admin))
        ->assertSessionHas('error');

    expect($this->admin->fresh()->state)->toBe('pending');
});

it('hides the review panel on an admins own profile', function () {
    $this->actingAs($this->admin)
        ->get(route('alumkit.users.show', $this->admin))
        ->assertOk()
        ->assertDontSee(__('alumkit::dashboard.transition_to_active'))
        ->assertDontSee(__('alumkit::dashboard.transition_to_rejected'));
});
