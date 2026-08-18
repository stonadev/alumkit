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
    $this->pendingUser->educations()->create(['level' => 'masters', 'institution' => 'MIT']);
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
