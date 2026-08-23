<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create();
    $this->user->profile()->create();
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'start_year' => 2015]);
    $this->user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);
});

it('renders dashboard nav links for users with the permission', function () {
    config(['alumkit.dashboard_nav' => [
        ['label' => 'Events', 'route' => 'alumkit.roles.index', 'permission' => 'manage roles'],
    ]]);

    Permission::findOrCreate('manage roles');
    $this->user->givePermissionTo('manage roles');

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee('Events');
});

it('hides dashboard nav links from users without the permission', function () {
    config(['alumkit.dashboard_nav' => [
        ['label' => 'Events', 'route' => 'alumkit.roles.index', 'permission' => 'manage roles'],
    ]]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertDontSee('Events');
});

it('renders dashboard nav links without a permission for any authenticated user', function () {
    config(['alumkit.dashboard_nav' => [
        ['label' => 'Events', 'route' => 'alumkit.roles.index'],
    ]]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee('Events');
});

it('renders dashboard nav groups with their children', function () {
    config(['alumkit.dashboard_nav' => [
        ['label' => 'Settings', 'permission' => 'manage roles', 'children' => [
            ['label' => 'General', 'route' => 'alumkit.roles.index'],
            ['label' => 'API', 'route' => 'alumkit.careers.index', 'permission' => 'manage roles'],
        ]],
    ]]);

    Permission::findOrCreate('manage roles');
    $this->user->givePermissionTo('manage roles');

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee('Settings')
        ->assertSee('General')
        ->assertSee('API');
});

it('hides an entire dashboard nav group without the group permission', function () {
    config(['alumkit.dashboard_nav' => [
        ['label' => 'Settings', 'permission' => 'manage roles', 'children' => [
            ['label' => 'General', 'route' => 'alumkit.roles.index'],
            ['label' => 'API', 'route' => 'alumkit.careers.index', 'permission' => 'manage roles'],
        ]],
    ]]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertDontSee('Settings');
});

it('hides a single child without the child permission', function () {
    config(['alumkit.dashboard_nav' => [
        ['label' => 'Settings', 'children' => [
            ['label' => 'General', 'route' => 'alumkit.roles.index'],
            ['label' => 'API', 'route' => 'alumkit.careers.index', 'permission' => 'manage careers'],
        ]],
    ]]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee('Settings')
        ->assertDontSee('API')
        ->assertSee('General');
});
