<?php

declare(strict_types=1);

use Alumkit\Alumkit\Models\Education;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create();
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT']);
    $this->user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);
});

it('renders the educations index for users with manage educations permission', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->get(route('alumkit.educations.index'))
        ->assertOk();
});

it('denies access to educations index for users without manage educations permission', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.educations.index'))
        ->assertForbidden();
});

it('renders the create education form for users with manage educations permission', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->get(route('alumkit.educations.create'))
        ->assertOk();
});

it('creates an education record', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'user_id' => $this->user->id,
            'level' => 'masters',
            'institution' => 'MIT',
            'subject' => 'Computer Science',
            'start_year' => 2020,
            'start_month' => 9,
            'end_year' => 2022,
            'end_month' => 6,
        ])
        ->assertRedirect(route('alumkit.educations.index'));

    $this->assertDatabaseHas('educations', [
        'user_id' => $this->user->id,
        'level' => 'masters',
        'institution' => 'MIT',
        'subject' => 'Computer Science',
        'start_year' => 2020,
        'start_month' => 9,
        'end_year' => 2022,
        'end_month' => 6,
    ]);
});

it('validates education required fields', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'user_id' => '',
            'level' => '',
            'institution' => '',
        ])
        ->assertSessionHasErrors(['user_id', 'level', 'institution']);
});

it('validates education level against config', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'user_id' => $this->user->id,
            'level' => 'invalid_level',
            'institution' => 'MIT',
        ])
        ->assertSessionHasErrors(['level']);
});

it('renders the edit education form', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'user_id' => $this->user->id,
        'level' => 'phd',
        'institution' => 'Stanford',
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.educations.edit', $education))
        ->assertOk();
});

it('updates an education record', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'user_id' => $this->user->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.educations.update', $education), [
            'level' => 'phd',
            'institution' => 'Stanford',
            'subject' => 'AI',
        ])
        ->assertRedirect(route('alumkit.educations.index'));

    $this->assertDatabaseHas('educations', [
        'id' => $education->id,
        'level' => 'phd',
        'institution' => 'Stanford',
        'subject' => 'AI',
    ]);
});

it('deletes an education record', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'user_id' => $this->user->id,
        'level' => 'diploma',
        'institution' => 'Community College',
    ]);

    $this->actingAs($this->user)
        ->delete(route('alumkit.educations.destroy', $education))
        ->assertRedirect(route('alumkit.educations.index'));

    $this->assertDatabaseMissing('educations', ['id' => $education->id]);
});

it('denies access to create education form without permission', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.educations.create'))
        ->assertForbidden();
});

it('denies access to store education without permission', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'user_id' => $this->user->id,
            'level' => 'masters',
            'institution' => 'MIT',
        ])
        ->assertForbidden();
});

it('denies access to edit education form without permission', function () {
    $education = Education::create([
        'user_id' => $this->user->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.educations.edit', $education))
        ->assertForbidden();
});

it('denies access to update education without permission', function () {
    $education = Education::create([
        'user_id' => $this->user->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.educations.update', $education), [
            'level' => 'phd',
            'institution' => 'Stanford',
        ])
        ->assertForbidden();
});

it('denies access to delete education without permission', function () {
    $education = Education::create([
        'user_id' => $this->user->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);

    $this->actingAs($this->user)
        ->delete(route('alumkit.educations.destroy', $education))
        ->assertForbidden();
});

it('validates non-existent user_id on store', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'user_id' => 99999,
            'level' => 'masters',
            'institution' => 'MIT',
        ])
        ->assertSessionHasErrors(['user_id']);
});

it('validates start_year digits on store', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'user_id' => $this->user->id,
            'level' => 'masters',
            'institution' => 'MIT',
            'start_year' => 20200,
        ])
        ->assertSessionHasErrors(['start_year']);
});

it('validates month range on store', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'user_id' => $this->user->id,
            'level' => 'masters',
            'institution' => 'MIT',
            'start_month' => 13,
        ])
        ->assertSessionHasErrors(['start_month']);
});

it('validates end_year gte start_year on store', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'user_id' => $this->user->id,
            'level' => 'masters',
            'institution' => 'MIT',
            'start_year' => 2022,
            'end_year' => 2020,
        ])
        ->assertSessionHasErrors(['end_year']);
});

it('validates level on update', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'user_id' => $this->user->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.educations.update', $education), [
            'level' => 'invalid_level',
            'institution' => 'MIT',
        ])
        ->assertSessionHasErrors(['level']);
});

it('validates institution required on update', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'user_id' => $this->user->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.educations.update', $education), [
            'level' => 'phd',
            'institution' => '',
        ])
        ->assertSessionHasErrors(['institution']);
});

it('validates end_year gte start_year on update', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'user_id' => $this->user->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.educations.update', $education), [
            'level' => 'phd',
            'institution' => 'Stanford',
            'start_year' => 2022,
            'end_year' => 2020,
        ])
        ->assertSessionHasErrors(['end_year']);
});
