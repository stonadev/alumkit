<?php

declare(strict_types=1);

use Alumkit\Alumkit\Enums\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->admin = User::factory()->approved()->create();
    $this->admin->profile()->create();
    $this->admin->givePermissionTo('manage members');

    $this->targetUser = User::factory()->create(['state' => UserState::Pending->value]);
    $this->targetUser->profile()->create();
});

it('shows the activity log with state transitions for admins', function () {
    activity('member_management')
        ->performedOn($this->targetUser)
        ->causedBy($this->admin)
        ->event('state_changed')
        ->withProperties(['old_state' => 'pending', 'new_state' => 'active'])
        ->log('member state changed');

    $this->actingAs($this->admin)
        ->get(route('alumkit.activity-log.index'))
        ->assertOk()
        ->assertSee('Membership state changed')
        ->assertSee($this->admin->name)
        ->assertSee($this->targetUser->name);
});

it('shows role changes with permission diffs', function () {
    $role = Role::findOrCreate('editor');

    activity('role_management')
        ->performedOn($role)
        ->causedBy($this->admin)
        ->event('updated')
        ->withProperties(['permissions_added' => ['manage members'], 'permissions_removed' => []])
        ->log('role updated');

    $this->actingAs($this->admin)
        ->get(route('alumkit.activity-log.index'))
        ->assertOk()
        ->assertSee('Role updated')
        ->assertSee('permissions_added')
        ->assertSee('manage members');
});

it('excludes trait-level CRUD noise from the feed', function () {
    activity('default')
        ->performedOn($this->targetUser)
        ->causedBy($this->admin)
        ->event('created')
        ->log('created');

    $this->actingAs($this->admin)
        ->get(route('alumkit.activity-log.index'))
        ->assertOk()
        ->assertDontSee('Records');
});

it('requires the manage members permission', function () {
    $this->actingAs($this->targetUser)
        ->get(route('alumkit.activity-log.index'))
        ->assertForbidden();
});

it('renders an empty state when there is no activity', function () {
    $this->actingAs($this->admin)
        ->get(route('alumkit.activity-log.index'))
        ->assertOk()
        ->assertSee('No activity yet');
});

it('paginates the activity feed', function () {
    $oldestUser = User::factory()->create(['state' => UserState::Pending->value]);
    $oldestUser->profile()->create();

    Activity::create([
        'log_name' => 'member_management',
        'description' => 'the oldest activity',
        'event' => 'state_changed',
        'subject_type' => $oldestUser::class,
        'subject_id' => $oldestUser->id,
        'causer_type' => $this->admin::class,
        'causer_id' => $this->admin->id,
        'created_at' => now()->subDay(),
    ]);

    for ($i = 0; $i < 20; $i++) {
        Activity::create([
            'log_name' => 'member_management',
            'description' => 'member state changed',
            'event' => 'state_changed',
            'subject_type' => $this->targetUser::class,
            'subject_id' => $this->targetUser->id,
            'causer_type' => $this->admin::class,
            'causer_id' => $this->admin->id,
            'created_at' => now()->subMinutes($i),
        ]);
    }

    $this->actingAs($this->admin)
        ->get(route('alumkit.activity-log.index'))
        ->assertOk()
        ->assertSee('Older')
        ->assertDontSee($oldestUser->name);

    $this->actingAs($this->admin)
        ->get(route('alumkit.activity-log.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Newer')
        ->assertSee($oldestUser->name);
});
