<?php

declare(strict_types=1);

use Alumkit\Alumkit\Enums\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create(['state' => 'registered']);
});

it('accepts a profile with no careers', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.profile.complete.store'), [
            'gender' => 'male',
            'blood_group' => 'O+',
            'website' => 'https://example.com',
            'date_of_birth' => '1990-05-15',
        ])
        ->assertRedirect(route('alumkit.dashboard'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('profiles', [
        'user_id' => $this->user->id,
        'gender' => 'male',
        'blood_group' => 'O+',
        'website' => 'https://example.com',
    ]);

    $this->assertDatabaseMissing('careers', ['profile_id' => $this->user->profile->id]);
});

it('accepts a profile with careers', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.profile.complete.store'), [
            'gender' => 'male',
            'blood_group' => 'O+',
            'careers' => [
                ['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020],
            ],
        ])
        ->assertRedirect(route('alumkit.dashboard'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('careers', [
        'profile_id' => $this->user->profile->id,
        'job_title' => 'Developer',
        'company' => 'Acme',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ]);
});

it('does not require careers to access protected routes', function () {
    $this->user->update(['state' => UserState::Pending->value]);
    $this->user->profile()->create(['gender' => 'male', 'blood_group' => 'O+']);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk();
});

it('redirects to profile completion when state is registered', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertRedirect(route('alumkit.profile.complete'));
});

it('still requires the admin to complete the profile', function () {
    $this->user->assignRole('admin');

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertRedirect(route('alumkit.profile.complete'));
});

it('does not show the approval banner to the admin after submission', function () {
    $this->user->assignRole('admin');

    $this->actingAs($this->user)
        ->post(route('alumkit.profile.complete.store'), [
            'gender' => 'male',
            'blood_group' => 'O+',
            'careers' => [
                ['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020],
            ],
        ])
        ->assertRedirect(route('alumkit.dashboard'))
        ->assertSessionMissing('status');

    $this->assertDatabaseHas('careers', [
        'profile_id' => $this->user->profile->id,
        'job_title' => 'Developer',
        'company' => 'Acme',
    ]);
});

it('shows "Update" on the form for non-admins', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.profile.complete'))
        ->assertOk()
        ->assertSee('Update');
});

it('shows "Submit" on the form for admins', function () {
    $this->user->assignRole('admin');

    $this->actingAs($this->user)
        ->get(route('alumkit.profile.complete'))
        ->assertOk()
        ->assertDontSee('Update')
        ->assertSee('Submit');
});

it('redirects away from the completion form once the profile is complete', function () {
    $this->user->update(['state' => UserState::Pending->value]);
    $this->user->profile()->create(['gender' => 'male', 'blood_group' => 'O+']);

    $this->actingAs($this->user)
        ->get(route('alumkit.profile.complete'))
        ->assertRedirect(route('alumkit.dashboard'));
});

it('does not write again when the profile is already complete', function () {
    $this->user->update(['state' => UserState::Pending->value]);
    $this->user->profile()->create(['gender' => 'male', 'blood_group' => 'O+']);
    $this->user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);

    $this->actingAs($this->user)
        ->post(route('alumkit.profile.complete.store'), [
            'careers' => [
                ['job_title' => 'CEO', 'company' => 'Other', 'employment_type' => 'full_time', 'start_year' => 2020],
            ],
        ])
        ->assertRedirect(route('alumkit.dashboard'));

    $this->assertDatabaseMissing('careers', ['job_title' => 'CEO']);
    $this->assertDatabaseHas('careers', [
        'profile_id' => $this->user->profile->id,
        'job_title' => 'Developer',
    ]);
});

it('restores submitted values when validation fails', function () {
    $this->actingAs($this->user)
        ->from(route('alumkit.profile.complete'))
        ->post(route('alumkit.profile.complete.store'), [
            'careers' => [
                ['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020],
            ],
            'website' => 'not-a-url',
            'date_of_birth' => '1990-01-01',
            'present_address' => 'Dhaka',
        ])
        ->assertSessionHasErrors(['website'])
        ->assertRedirect(route('alumkit.profile.complete'));

    $this->get(route('alumkit.profile.complete'))
        ->assertOk()
        ->assertSee('Developer')
        ->assertSee('Dhaka')
        ->assertSee('1990-01-01');
});
