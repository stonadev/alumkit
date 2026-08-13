<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create();
});

it('accepts a profile with education but no careers', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.profile.complete.store'), [
            'educations' => [
                ['level' => 'masters', 'institution' => 'MIT'],
            ],
        ])
        ->assertRedirect(route('alumkit.dashboard'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('educations', [
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);

    $this->assertDatabaseMissing('careers', ['profile_id' => $this->user->profile->id]);
});

it('accepts a profile with education and careers', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.profile.complete.store'), [
            'educations' => [
                ['level' => 'masters', 'institution' => 'MIT'],
            ],
            'careers' => [
                ['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020],
            ],
        ])
        ->assertRedirect(route('alumkit.dashboard'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('educations', [
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);

    $this->assertDatabaseHas('careers', [
        'profile_id' => $this->user->profile->id,
        'job_title' => 'Developer',
        'company' => 'Acme',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ]);
});

it('rejects a profile without education', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.profile.complete.store'), [
            'educations' => [],
        ])
        ->assertSessionHasErrors(['educations']);
});

it('does not require careers to access protected routes', function () {
    $this->user->profile()->create();
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT']);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk();
});

it('redirects to profile completion when education is missing', function () {
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
            'educations' => [
                ['level' => 'masters', 'institution' => 'MIT'],
            ],
        ])
        ->assertRedirect(route('alumkit.dashboard'))
        ->assertSessionMissing('status');

    $this->assertDatabaseHas('educations', [
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);
});

it('shows "Submit for Approval" on the form for non-admins', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.profile.complete'))
        ->assertOk()
        ->assertSee('Submit for Approval');
});

it('shows "Submit" on the form for admins', function () {
    $this->user->assignRole('admin');

    $this->actingAs($this->user)
        ->get(route('alumkit.profile.complete'))
        ->assertOk()
        ->assertDontSee('Submit for Approval')
        ->assertSee('Submit');
});
