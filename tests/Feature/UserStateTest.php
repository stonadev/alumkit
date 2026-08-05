<?php

declare(strict_types=1);

use Alumkit\Alumkit\Enums\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create(['state' => UserState::Active->value]);
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT']);
    $this->user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);

    $this->targetUser = User::factory()->create(['state' => UserState::Pending->value]);
    $this->targetUser->educations()->create(['level' => 'masters', 'institution' => 'MIT']);
    $this->targetUser->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);
});

it('allows pending to active transition', function () {
    expect(UserState::Pending->canTransitionTo(UserState::Active))->toBeTrue();
});

it('allows pending to rejected transition', function () {
    expect(UserState::Pending->canTransitionTo(UserState::Rejected))->toBeTrue();
});

it('allows active to suspended transition', function () {
    expect(UserState::Active->canTransitionTo(UserState::Suspended))->toBeTrue();
});

it('allows suspended to active transition', function () {
    expect(UserState::Suspended->canTransitionTo(UserState::Active))->toBeTrue();
});

it('blocks invalid transitions', function () {
    expect(UserState::Active->canTransitionTo(UserState::Pending))->toBeFalse();
    expect(UserState::Active->canTransitionTo(UserState::Rejected))->toBeFalse();
    expect(UserState::Rejected->canTransitionTo(UserState::Active))->toBeFalse();
    expect(UserState::Rejected->canTransitionTo(UserState::Pending))->toBeFalse();
    expect(UserState::Suspended->canTransitionTo(UserState::Pending))->toBeFalse();
    expect(UserState::Suspended->canTransitionTo(UserState::Rejected))->toBeFalse();
});

it('sets default state on user creation', function () {
    $newUser = User::factory()->create();

    expect($newUser->state)->toBe(UserState::Pending->value);
});

it('updates user state with manage members permission', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Active->value,
        ])
        ->assertRedirect(route('alumkit.users.index'))
        ->assertSessionHas('status');

    expect($this->targetUser->fresh()->state)->toBe(UserState::Active->value);
});

it('denies state update without manage members permission', function () {
    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Active->value,
        ])
        ->assertForbidden();
});

it('rejects invalid state transition', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->targetUser->update(['state' => UserState::Active->value]);

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Pending->value,
        ])
        ->assertRedirect(route('alumkit.users.index'))
        ->assertSessionHas('error');

    expect($this->targetUser->fresh()->state)->toBe(UserState::Active->value);
});

it('validates state parameter', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => 'invalid',
        ])
        ->assertSessionHasErrors('state');
});

it('blocks suspended user from accessing dashboard', function () {
    $this->user->update(['state' => UserState::Suspended->value]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertRedirect(route('login'));
});

it('blocks rejected user from accessing dashboard', function () {
    $this->user->update(['state' => UserState::Rejected->value]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertRedirect(route('login'));
});

it('allows active user to access dashboard', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk();
});

it('allows pending user to access dashboard', function () {
    $this->user->update(['state' => UserState::Pending->value]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk();
});
