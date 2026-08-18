<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create();
    $this->user->profile()->create();
    $this->actingAs($this->user);
});

it('renders all four profile tab labels', function () {
    $this->get(route('alumkit.profile'))
        ->assertOk()
        ->assertSee('href="#profile"', false)
        ->assertSee('href="#education"', false)
        ->assertSee('href="#career"', false)
        ->assertSee('href="#security"', false);
});

it('lists only the authenticated users education records', function () {
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT']);

    $other = User::factory()->create();
    $other->profile()->create();
    $other->educations()->create(['level' => 'phd', 'institution' => 'Oxford']);

    $this->get(route('alumkit.profile'))
        ->assertOk()
        ->assertSee('MIT')
        ->assertDontSee('Oxford');
});

it('lets a member without manage educations permission open the create form', function () {
    $this->get(route('alumkit.profile.educations.create'))
        ->assertOk();
});

it('stores an education record on the authenticated users profile', function () {
    $this->post(route('alumkit.profile.educations.store'), [
        'level' => 'masters',
        'institution' => 'MIT',
        'subject' => 'Computer Science',
        'start_year' => 2015,
        'start_month' => 9,
        'end_year' => 2019,
        'end_month' => 6,
    ])
        ->assertRedirect(route('alumkit.profile').'#education')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('educations', [
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);
});

it('resubmits a rejected user for review when education changes', function () {
    $this->user->update(['state' => 'rejected']);

    $this->post(route('alumkit.profile.educations.store'), [
        'level' => 'masters',
        'institution' => 'MIT',
    ])
        ->assertRedirect(route('alumkit.profile').'#education');

    expect($this->user->fresh()->state)->toBe('pending');
});

it('validates required education fields on store', function () {
    $this->post(route('alumkit.profile.educations.store'), [
        'level' => '',
        'institution' => '',
    ])
        ->assertSessionHasErrors(['level', 'institution']);
});

it('updates an education record on the authenticated users profile', function () {
    $education = $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT']);

    $this->put(route('alumkit.profile.educations.update', $education), [
        'level' => 'phd',
        'institution' => 'Stanford',
    ])
        ->assertRedirect(route('alumkit.profile').'#education')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('educations', [
        'id' => $education->id,
        'profile_id' => $this->user->profile->id,
        'level' => 'phd',
        'institution' => 'Stanford',
    ]);
});

it('cannot update another users education record', function () {
    $other = User::factory()->create();
    $other->profile()->create();
    $otherEducation = $other->educations()->create(['level' => 'phd', 'institution' => 'Oxford']);

    $this->put(route('alumkit.profile.educations.update', $otherEducation), [
        'level' => 'masters',
        'institution' => 'MIT',
    ])
        ->assertNotFound();
});

it('cannot delete another users education record', function () {
    $other = User::factory()->create();
    $other->profile()->create();
    $otherEducation = $other->educations()->create(['level' => 'phd', 'institution' => 'Oxford']);

    $this->delete(route('alumkit.profile.educations.destroy', $otherEducation))
        ->assertNotFound();

    $this->assertDatabaseHas('educations', ['id' => $otherEducation->id]);
});

it('deletes an education record on the authenticated users profile', function () {
    $education = $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT']);

    $this->delete(route('alumkit.profile.educations.destroy', $education))
        ->assertRedirect(route('alumkit.profile').'#education')
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('educations', ['id' => $education->id]);
});

it('cannot open the edit form for another users education record', function () {
    $other = User::factory()->create();
    $other->profile()->create();
    $otherEducation = $other->educations()->create(['level' => 'phd', 'institution' => 'Oxford']);

    $this->get(route('alumkit.profile.educations.edit', $otherEducation))
        ->assertNotFound();
});

it('validates education fields on update', function () {
    $education = $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT']);

    $this->put(route('alumkit.profile.educations.update', $education), [
        'level' => '',
        'institution' => '',
    ])->assertSessionHasErrors(['level', 'institution']);
});

it('rejects an end_year before start_year', function () {
    $this->post(route('alumkit.profile.educations.store'), [
        'level' => 'masters',
        'institution' => 'MIT',
        'start_year' => 2019,
        'end_year' => 2015,
    ])->assertSessionHasErrors(['end_year']);
});

it('redirects guests away from the education routes', function () {
    Auth::logout();

    $this->get(route('alumkit.profile.educations.create'))
        ->assertRedirect(route('login'));
});
