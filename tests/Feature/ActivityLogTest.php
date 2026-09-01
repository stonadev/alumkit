<?php

declare(strict_types=1);

use Alumkit\Alumkit\Enums\UserState;
use Alumkit\Alumkit\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
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

it('logs a state transition with causer, subject and old/new state', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Active->value,
        ])
        ->assertRedirect(route('alumkit.users.index'));

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'member_management',
        'description' => 'member state changed',
        'event' => 'state_changed',
        'subject_type' => $this->targetUser::class,
        'subject_id' => $this->targetUser->id,
        'causer_type' => $this->user::class,
        'causer_id' => $this->user->id,
    ]);

    $activity = Activity::where('event', 'state_changed')->firstOrFail();

    expect($activity->properties->toArray())->toBe([
        'old_state' => UserState::Pending->value,
        'new_state' => UserState::Active->value,
    ]);
});

it('does not log an invalid state transition', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    $this->targetUser->update(['state' => UserState::Active->value]);

    $this->actingAs($this->user)
        ->put(route('alumkit.users.state.update', $this->targetUser), [
            'state' => UserState::Pending->value,
        ])
        ->assertSessionHas('error');

    $this->assertDatabaseMissing('activity_log', ['event' => 'state_changed']);
});

it('logs role creation with permissions', function () {
    Permission::findOrCreate('manage roles');
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage roles');

    $this->actingAs($this->user)
        ->post(route('alumkit.roles.store'), [
            'name' => 'editor',
            'permissions' => ['manage members'],
        ])
        ->assertRedirect(route('alumkit.roles.index'));

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'role_management',
        'event' => 'created',
        'subject_type' => Role::class,
        'causer_id' => $this->user->id,
    ]);

    $activity = Activity::where('event', 'created')->where('log_name', 'role_management')->firstOrFail();

    expect($activity->properties->toArray())->toBe(['permissions' => ['manage members']]);
});

it('logs role update with permission diff', function () {
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

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'role_management',
        'event' => 'updated',
        'subject_id' => $role->id,
        'causer_id' => $this->user->id,
    ]);

    $activity = Activity::where('event', 'updated')->where('log_name', 'role_management')->firstOrFail();

    expect($activity->properties->toArray())->toBe([
        'permissions_added' => ['view dashboard'],
        'permissions_removed' => ['manage members'],
    ]);
});

it('logs role deletion with the role name in properties', function () {
    Permission::findOrCreate('manage roles');
    $this->user->givePermissionTo('manage roles');

    $role = Role::findOrCreate('temp-role');

    $this->actingAs($this->user)
        ->delete(route('alumkit.roles.destroy', $role))
        ->assertRedirect(route('alumkit.roles.index'));

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'role_management',
        'description' => 'role deleted',
        'event' => 'deleted',
        'causer_id' => $this->user->id,
    ]);

    $activity = Activity::where('event', 'deleted')->where('log_name', 'role_management')->firstOrFail();

    expect($activity->properties->toArray())->toBe(['role_name' => 'temp-role']);
});

it('logs user role sync with added and removed diff', function () {
    Permission::findOrCreate('manage members');
    $this->user->givePermissionTo('manage members');

    Role::findOrCreate('admin');
    Role::findOrCreate('member');

    $this->targetUser->syncRoles(['admin']);

    $this->actingAs($this->user)
        ->put(route('alumkit.users.roles.update', $this->targetUser), [
            'roles' => ['admin', 'member'],
        ])
        ->assertRedirect(route('alumkit.users.roles.edit', $this->targetUser));

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'member_management',
        'description' => 'roles updated',
        'event' => 'roles_synced',
        'subject_type' => $this->targetUser::class,
        'subject_id' => $this->targetUser->id,
        'causer_id' => $this->user->id,
    ]);

    $activity = Activity::where('event', 'roles_synced')->firstOrFail();

    expect($activity->properties->toArray())->toBe([
        'roles_added' => ['member'],
        'roles_removed' => [],
    ]);
});

it('logs profile submission on completion', function () {
    $this->user->profile()->delete();
    $this->user->educations()->delete();
    $this->user->careers()->delete();

    $this->actingAs($this->user)
        ->post(route('alumkit.profile.complete.store'), [
            'educations' => [
                ['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2020, 'is_current' => 1],
            ],
        ])
        ->assertRedirect(route('alumkit.dashboard'));

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'profile',
        'description' => 'profile submitted',
        'event' => 'submitted',
        'subject_type' => $this->user::class,
        'subject_id' => $this->user->id,
        'causer_id' => $this->user->id,
    ]);
});

it('logs profile resubmission when a rejected user edits details', function () {
    $this->user->update(['state' => UserState::Rejected->value]);

    $this->actingAs($this->user)
        ->put(route('alumkit.profile.details.update'), [
            'website' => 'https://example.com',
        ])
        ->assertRedirect(route('alumkit.profile'));

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'profile',
        'description' => 'profile resubmitted for review',
        'event' => 'resubmitted',
        'subject_type' => $this->user::class,
        'subject_id' => $this->user->id,
        'causer_id' => $this->user->id,
    ]);
});

it('logs a password change through the fortify update action', function () {
    $this->user->forceFill(['password' => 'current-password'])->save();

    $this->actingAs($this->user)
        ->put(route('user-password.update'), [
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'auth',
        'description' => 'password changed',
        'event' => 'password_changed',
        'causer_type' => $this->user::class,
        'causer_id' => $this->user->id,
    ]);
});

it('logs an education creation through the trait', function () {
    $this->actingAs($this->user);

    $education = $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'default',
        'description' => 'created',
        'event' => 'created',
        'subject_type' => $education::class,
        'subject_id' => $education->id,
        'causer_id' => $this->user->id,
    ]);
});

it('logs a post creation through the trait', function () {
    $this->actingAs($this->user);

    $post = Post::create([
        'user_id' => $this->user->id,
        'title' => 'Hello',
        'body' => 'World',
    ]);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'default',
        'description' => 'created',
        'event' => 'created',
        'subject_type' => $post::class,
        'subject_id' => $post->id,
        'causer_id' => $this->user->id,
    ]);
});
