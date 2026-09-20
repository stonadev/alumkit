<?php

declare(strict_types=1);

use Alumkit\Alumkit\Models\CommitteeMember;
use Alumkit\Alumkit\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('renders crop UI on profile details page', function () {
    $user = User::factory()->approved()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('alumkit.profile'))
        ->assertOk()
        ->assertSee('alumkit-cropper.js', false)
        ->assertSee(__('alumkit::messages.crop'), false);
});

it('renders crop UI on committee create page', function () {
    $user = User::factory()->approved()->withProfile()->create();

    Permission::findOrCreate('manage committee');
    $user->givePermissionTo('manage committee');

    $this->actingAs($user)
        ->get(route('alumkit.committee.create'))
        ->assertOk()
        ->assertSee('alumkit-cropper.js', false)
        ->assertSee(__('alumkit::messages.crop'), false);
});

it('renders crop UI on committee edit page', function () {
    $user = User::factory()->approved()->withProfile()->create();

    Permission::findOrCreate('manage committee');
    $user->givePermissionTo('manage committee');

    $position = Position::create(['name' => 'President']);
    $member = CommitteeMember::create([
        'position_id' => $position->id,
        'name' => 'Test Member',
    ]);

    $this->actingAs($user)
        ->get(route('alumkit.committee.edit', $member))
        ->assertOk()
        ->assertSee('alumkit-cropper.js', false)
        ->assertSee(__('alumkit::messages.crop'), false);
});

it('renders crop UI on profile completion page', function () {
    $user = User::factory()->create(['state' => 'registered']);

    $this->actingAs($user)
        ->get(route('alumkit.profile.complete'))
        ->assertOk()
        ->assertSee('alumkit-cropper.js', false);
});
