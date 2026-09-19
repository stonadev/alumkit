<?php

declare(strict_types=1);

use Alumkit\Alumkit\Enums\UserState;
use Alumkit\Alumkit\Notifications\UserActivatedNotification;
use Alumkit\Alumkit\Notifications\UserRejectedNotification;
use Alumkit\Alumkit\Notifications\UserSuspendedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create(['state' => UserState::Active->value]);
    $this->user->profile()->create();
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);
    $this->user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);

    $this->targetUser = User::factory()->create(['state' => UserState::Pending->value]);
    $this->targetUser->profile()->create();
    $this->targetUser->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);
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

it('allows rejected to pending transition', function () {
    expect(UserState::Rejected->canTransitionTo(UserState::Pending))->toBeTrue();
});

it('blocks invalid transitions', function () {
    expect(UserState::Active->canTransitionTo(UserState::Pending))->toBeFalse();
    expect(UserState::Active->canTransitionTo(UserState::Rejected))->toBeFalse();
    expect(UserState::Active->canTransitionTo(UserState::Registered))->toBeFalse();
    expect(UserState::Rejected->canTransitionTo(UserState::Active))->toBeFalse();
    expect(UserState::Suspended->canTransitionTo(UserState::Pending))->toBeFalse();
    expect(UserState::Suspended->canTransitionTo(UserState::Rejected))->toBeFalse();
});

it('has no admin transitions from registered state', function () {
    expect(UserState::Registered->transitions())->toBe([]);
    expect(UserState::Registered->canTransitionTo(UserState::Pending))->toBeFalse();
});

it('sets default state on user creation', function () {
    $newUser = User::factory()->create();

    expect($newUser->state)->toBe(UserState::Registered->value);
});

it('updates user state with manage members permission', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Active->value,
            'reason' => 'Approved after review',
        ])
        ->assertRedirect(route('alumkit.users.index'))
        ->assertSessionHas('status');

    expect($this->targetUser->fresh()->state)->toBe(UserState::Active->value);
});

it('re-queues a rejected user via manage members permission', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->targetUser->update(['state' => UserState::Rejected->value]);

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Pending->value,
            'reason' => 'Requeue for review',
        ])
        ->assertRedirect(route('alumkit.users.index'))
        ->assertSessionHas('status');

    expect($this->targetUser->fresh()->state)->toBe(UserState::Pending->value);
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
            'reason' => 'Some reason',
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

it('allows suspended user to access dashboard', function () {
    Permission::findOrCreate('manage roles');
    $this->user->givePermissionTo('manage roles');
    $this->user->update(['state' => UserState::Suspended->value]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee(__('alumkit::dashboard.account_suspended'))
        ->assertDontSee(__('alumkit::dashboard.quick_links'))
        ->assertDontSee(route('alumkit.profile'));
});

it('redirects suspended user away from profile', function () {
    $this->user->update(['state' => UserState::Suspended->value]);

    $this->actingAs($this->user)
        ->get(route('alumkit.profile'))
        ->assertRedirect(route('alumkit.dashboard'));
});

it('allows rejected user to access dashboard', function () {
    $this->user->update(['state' => UserState::Rejected->value]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk();
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

it('requires reason when rejecting', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Rejected->value,
        ])
        ->assertSessionHasErrors('reason');
});

it('requires reason when suspending', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');
    $this->targetUser->update(['state' => UserState::Active->value]);

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Suspended->value,
        ])
        ->assertSessionHasErrors('reason');
});

it('does not require reason when activating', function () {
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

it('sends rejection email with reason', function () {
    Notification::fake();
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Rejected->value,
            'reason' => 'Incomplete application',
        ])
        ->assertRedirect(route('alumkit.users.index'));

    Notification::assertSentTo(
        $this->targetUser,
        UserRejectedNotification::class,
        function ($notification, $channels) {
            return $notification->reason === 'Incomplete application';
        },
    );
});

it('sends suspension email with reason', function () {
    Notification::fake();
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');
    $this->targetUser->update(['state' => UserState::Active->value]);

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Suspended->value,
            'reason' => 'Violation of terms',
        ])
        ->assertRedirect(route('alumkit.users.index'));

    Notification::assertSentTo(
        $this->targetUser,
        UserSuspendedNotification::class,
        function ($notification, $channels) {
            return $notification->reason === 'Violation of terms';
        },
    );
});

it('sends activation email when approving a pending user', function () {
    Notification::fake();
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Active->value,
        ])
        ->assertRedirect(route('alumkit.users.index'));

    Notification::assertSentTo(
        $this->targetUser,
        UserActivatedNotification::class,
        function ($notification, $channels) {
            return $notification->via($this->targetUser) === ['mail']
                && $notification->toMail($this->targetUser)->actionUrl === route('alumkit.dashboard');
        },
    );
});

it('sends activation email when reactivating a suspended user', function () {
    Notification::fake();
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');
    $this->targetUser->update(['state' => UserState::Suspended->value]);

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Active->value,
        ])
        ->assertRedirect(route('alumkit.users.index'));

    Notification::assertSentTo($this->targetUser, UserActivatedNotification::class);
});

it('stores reason in activity log', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Rejected->value,
            'reason' => 'Policy violation',
        ])
        ->assertRedirect(route('alumkit.users.index'));

    $activity = Activity::where('subject_id', $this->targetUser->getKey())
        ->where('event', 'state_changed')
        ->latest()
        ->first();
    expect($activity->properties->get('reason'))->toBe('Policy violation');
});

it('rejects reason exceeding max length', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Rejected->value,
            'reason' => str_repeat('a', 2001),
        ])
        ->assertSessionHasErrors('reason');
});

it('accepts reason at max length boundary', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Rejected->value,
            'reason' => str_repeat('a', 2000),
        ])
        ->assertRedirect(route('alumkit.users.index'))
        ->assertSessionHas('status');

    expect($this->targetUser->fresh()->state)->toBe(UserState::Rejected->value);
});
