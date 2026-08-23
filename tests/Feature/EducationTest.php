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
    $this->user->profile()->create();
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'start_year' => 2015]);
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

it('renders level, institution and subject suggestions from config on the create form', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    config()->set('alumkit.education.levels', ['Honors', 'Trade School']);
    config()->set('alumkit.education.institutions', ['MIT', 'Stanford']);
    config()->set('alumkit.education.subjects', ['Computer Science', 'Physics']);

    $this->actingAs($this->user)
        ->get(route('alumkit.educations.create'))
        ->assertOk()
        ->assertSee('Honors', false)
        ->assertSee('Trade School', false)
        ->assertSee('MIT', false)
        ->assertSee('Stanford', false)
        ->assertSee('role="listbox"', false)
        ->assertSee('Computer Science', false)
        ->assertSee('Physics', false);
});

it('creates an education record', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'profile_id' => $this->user->profile->id,
            'level' => 'masters',
            'institution' => 'MIT',
            'student_id' => 'STU-2020-001',
            'subject' => 'Computer Science',
            'start_year' => 2020,
            'start_month' => 9,
            'end_year' => 2022,
            'end_month' => 6,
        ])
        ->assertRedirect(route('alumkit.educations.index'));

    $this->assertDatabaseHas('educations', [
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
        'student_id' => 'STU-2020-001',
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
            'profile_id' => '',
            'level' => '',
            'institution' => '',
        ])
        ->assertSessionHasErrors(['profile_id', 'level', 'institution', 'start_year']);
});

it('accepts a custom education level not listed in config', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'profile_id' => $this->user->profile->id,
            'level' => 'Trade School',
            'institution' => 'MIT',
            'start_year' => 2020,
        ])
        ->assertRedirect(route('alumkit.educations.index'));

    $this->assertDatabaseHas('educations', [
        'profile_id' => $this->user->profile->id,
        'level' => 'Trade School',
        'institution' => 'MIT',
    ]);
});

it('renders the edit education form', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'profile_id' => $this->user->profile->id,
        'level' => 'phd',
        'institution' => 'Stanford',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.educations.edit', $education))
        ->assertOk();
});

it('updates an education record', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.educations.update', $education), [
            'level' => 'phd',
            'institution' => 'Stanford',
            'student_id' => 'STU-2020-002',
            'subject' => 'AI',
            'start_year' => 2020,
        ])
        ->assertRedirect(route('alumkit.educations.index'));

    $this->assertDatabaseHas('educations', [
        'id' => $education->id,
        'level' => 'phd',
        'institution' => 'Stanford',
        'student_id' => 'STU-2020-002',
        'subject' => 'AI',
    ]);
});

it('deletes an education record', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'profile_id' => $this->user->profile->id,
        'level' => 'diploma',
        'institution' => 'Community College',
        'start_year' => 2020,
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
            'profile_id' => $this->user->profile->id,
            'level' => 'masters',
            'institution' => 'MIT',
        ])
        ->assertForbidden();
});

it('denies access to edit education form without permission', function () {
    $education = Education::create([
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.educations.edit', $education))
        ->assertForbidden();
});

it('denies access to update education without permission', function () {
    $education = Education::create([
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
        'start_year' => 2020,
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
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
        'start_year' => 2020,
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
            'profile_id' => 99999,
            'level' => 'masters',
            'institution' => 'MIT',
            'start_year' => 2020,
        ])
        ->assertSessionHasErrors(['profile_id']);
});

it('validates start_year digits on store', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'profile_id' => $this->user->profile->id,
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
            'profile_id' => $this->user->profile->id,
            'level' => 'masters',
            'institution' => 'MIT',
            'start_year' => 2020,
            'start_month' => 13,
        ])
        ->assertSessionHasErrors(['start_month']);
});

it('validates end_year gte start_year on store', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'profile_id' => $this->user->profile->id,
            'level' => 'masters',
            'institution' => 'MIT',
            'start_year' => 2022,
            'end_year' => 2020,
        ])
        ->assertSessionHasErrors(['end_year']);
});

it('accepts a custom education level on update', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.educations.update', $education), [
            'level' => 'Trade School',
            'institution' => 'MIT',
            'start_year' => 2020,
        ])
        ->assertRedirect(route('alumkit.educations.index'));

    $this->assertDatabaseHas('educations', [
        'id' => $education->id,
        'level' => 'Trade School',
        'institution' => 'MIT',
    ]);
});

it('validates institution required on update', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $education = Education::create([
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
        'start_year' => 2020,
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
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
        'start_year' => 2020,
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

it('accepts is_current with null end_year on store', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'profile_id' => $this->user->profile->id,
            'level' => 'masters',
            'institution' => 'MIT',
            'start_year' => 2020,
            'is_current' => true,
        ])
        ->assertRedirect(route('alumkit.educations.index'));

    $this->assertDatabaseHas('educations', [
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'is_current' => true,
        'end_year' => null,
    ]);
});

it('validates start_year required on store', function () {
    Permission::findOrCreate('manage educations');
    $this->user->givePermissionTo('manage educations');

    $this->actingAs($this->user)
        ->post(route('alumkit.educations.store'), [
            'profile_id' => $this->user->profile->id,
            'level' => 'masters',
            'institution' => 'MIT',
        ])
        ->assertSessionHasErrors(['start_year']);
});
