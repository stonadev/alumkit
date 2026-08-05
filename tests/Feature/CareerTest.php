<?php

declare(strict_types=1);

use Alumkit\Alumkit\Models\Career;
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

it('renders the careers index for users with manage careers permission', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $this->actingAs($this->user)
        ->get(route('alumkit.careers.index'))
        ->assertOk();
});

it('denies access to careers index for users without manage careers permission', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.careers.index'))
        ->assertForbidden();
});

it('renders the create career form for users with manage careers permission', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $this->actingAs($this->user)
        ->get(route('alumkit.careers.create'))
        ->assertOk();
});

it('creates a career record', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $this->actingAs($this->user)
        ->post(route('alumkit.careers.store'), [
            'user_id' => $this->user->id,
            'job_title' => 'Software Engineer',
            'company' => 'Google',
            'employment_type' => 'full_time',
            'industry' => 'Technology',
            'location' => 'Mountain View, CA',
            'start_year' => 2020,
            'start_month' => 1,
            'end_year' => 2022,
            'end_month' => 6,
            'description' => 'Building search infrastructure.',
        ])
        ->assertRedirect(route('alumkit.careers.index'));

    $this->assertDatabaseHas('careers', [
        'user_id' => $this->user->id,
        'job_title' => 'Software Engineer',
        'company' => 'Google',
        'employment_type' => 'full_time',
        'industry' => 'Technology',
        'location' => 'Mountain View, CA',
        'start_year' => 2020,
        'start_month' => 1,
        'end_year' => 2022,
        'end_month' => 6,
        'description' => 'Building search infrastructure.',
    ]);
});

it('validates career required fields', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $this->actingAs($this->user)
        ->post(route('alumkit.careers.store'), [
            'user_id' => '',
            'job_title' => '',
            'company' => '',
            'employment_type' => '',
        ])
        ->assertSessionHasErrors(['user_id', 'job_title', 'company', 'employment_type']);
});

it('validates career employment type against enum', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $this->actingAs($this->user)
        ->post(route('alumkit.careers.store'), [
            'user_id' => $this->user->id,
            'job_title' => 'Engineer',
            'company' => 'Google',
            'employment_type' => 'invalid_type',
        ])
        ->assertSessionHasErrors(['employment_type']);
});

it('validates end_year format', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $this->actingAs($this->user)
        ->post(route('alumkit.careers.store'), [
            'user_id' => $this->user->id,
            'job_title' => 'Engineer',
            'company' => 'Google',
            'employment_type' => 'full_time',
            'end_year' => 'invalid',
        ])
        ->assertSessionHasErrors(['end_year']);
});

it('accepts is_current for current position', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $this->actingAs($this->user)
        ->post(route('alumkit.careers.store'), [
            'user_id' => $this->user->id,
            'job_title' => 'Engineer',
            'company' => 'Google',
            'employment_type' => 'full_time',
            'start_year' => 2024,
            'is_current' => true,
        ])
        ->assertRedirect(route('alumkit.careers.index'));

    $this->assertDatabaseHas('careers', [
        'user_id' => $this->user->id,
        'is_current' => true,
        'end_year' => null,
    ]);
});

it('renders the edit career form', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $career = Career::create([
        'user_id' => $this->user->id,
        'job_title' => 'Developer',
        'company' => 'Microsoft',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.careers.edit', $career))
        ->assertOk();
});

it('updates a career record', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $career = Career::create([
        'user_id' => $this->user->id,
        'job_title' => 'Developer',
        'company' => 'Microsoft',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.careers.update', $career), [
            'job_title' => 'Senior Developer',
            'company' => 'Google',
            'employment_type' => 'contract',
            'start_year' => 2020,
            'end_year' => 2024,
        ])
        ->assertRedirect(route('alumkit.careers.index'));

    $this->assertDatabaseHas('careers', [
        'id' => $career->id,
        'job_title' => 'Senior Developer',
        'company' => 'Google',
        'employment_type' => 'contract',
        'end_year' => 2024,
    ]);
});

it('deletes a career record', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $career = Career::create([
        'user_id' => $this->user->id,
        'job_title' => 'Intern',
        'company' => 'Startup',
        'employment_type' => 'internship',
        'start_year' => 2023,
    ]);

    $this->actingAs($this->user)
        ->delete(route('alumkit.careers.destroy', $career))
        ->assertRedirect(route('alumkit.careers.index'));

    $this->assertDatabaseMissing('careers', ['id' => $career->id]);
});

it('denies access to create career form without permission', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.careers.create'))
        ->assertForbidden();
});

it('denies access to store career without permission', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.careers.store'), [
            'user_id' => $this->user->id,
            'job_title' => 'Engineer',
            'company' => 'Google',
            'employment_type' => 'full_time',
        ])
        ->assertForbidden();
});

it('denies access to edit career form without permission', function () {
    $career = Career::create([
        'user_id' => $this->user->id,
        'job_title' => 'Developer',
        'company' => 'Microsoft',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.careers.edit', $career))
        ->assertForbidden();
});

it('denies access to update career without permission', function () {
    $career = Career::create([
        'user_id' => $this->user->id,
        'job_title' => 'Developer',
        'company' => 'Microsoft',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.careers.update', $career), [
            'job_title' => 'Senior Developer',
            'company' => 'Google',
            'employment_type' => 'full_time',
        ])
        ->assertForbidden();
});

it('denies access to delete career without permission', function () {
    $career = Career::create([
        'user_id' => $this->user->id,
        'job_title' => 'Developer',
        'company' => 'Microsoft',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->delete(route('alumkit.careers.destroy', $career))
        ->assertForbidden();
});

it('validates non-existent user_id on store', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $this->actingAs($this->user)
        ->post(route('alumkit.careers.store'), [
            'user_id' => 99999,
            'job_title' => 'Engineer',
            'company' => 'Google',
            'employment_type' => 'full_time',
        ])
        ->assertSessionHasErrors(['user_id']);
});

it('validates start_year digits on store', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $this->actingAs($this->user)
        ->post(route('alumkit.careers.store'), [
            'user_id' => $this->user->id,
            'job_title' => 'Engineer',
            'company' => 'Google',
            'employment_type' => 'full_time',
            'start_year' => 20,
        ])
        ->assertSessionHasErrors(['start_year']);
});

it('validates month range on store', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $this->actingAs($this->user)
        ->post(route('alumkit.careers.store'), [
            'user_id' => $this->user->id,
            'job_title' => 'Engineer',
            'company' => 'Google',
            'employment_type' => 'full_time',
            'start_month' => 13,
        ])
        ->assertSessionHasErrors(['start_month']);
});

it('validates employment_type on update', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $career = Career::create([
        'user_id' => $this->user->id,
        'job_title' => 'Developer',
        'company' => 'Microsoft',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.careers.update', $career), [
            'job_title' => 'Developer',
            'company' => 'Microsoft',
            'employment_type' => 'invalid',
        ])
        ->assertSessionHasErrors(['employment_type']);
});

it('validates job_title required on update', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $career = Career::create([
        'user_id' => $this->user->id,
        'job_title' => 'Developer',
        'company' => 'Microsoft',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.careers.update', $career), [
            'job_title' => '',
            'company' => 'Microsoft',
            'employment_type' => 'full_time',
        ])
        ->assertSessionHasErrors(['job_title']);
});

it('validates end_year format on update', function () {
    Permission::findOrCreate('manage careers');
    $this->user->givePermissionTo('manage careers');

    $career = Career::create([
        'user_id' => $this->user->id,
        'job_title' => 'Developer',
        'company' => 'Microsoft',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.careers.update', $career), [
            'job_title' => 'Developer',
            'company' => 'Microsoft',
            'employment_type' => 'full_time',
            'end_year' => 'nope',
        ])
        ->assertSessionHasErrors(['end_year']);
});
