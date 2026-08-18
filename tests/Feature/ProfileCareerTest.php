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

it('lists only the authenticated users career records', function () {
    $this->user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);

    $other = User::factory()->create();
    $other->profile()->create();
    $other->careers()->create(['job_title' => 'Designer', 'company' => 'Globex', 'employment_type' => 'contract', 'start_year' => 2018]);

    $this->get(route('alumkit.profile'))
        ->assertOk()
        ->assertSee('Acme')
        ->assertDontSee('Globex');
});

it('lets a member without manage careers permission open the create form', function () {
    $this->get(route('alumkit.profile.careers.create'))
        ->assertOk();
});

it('stores a career record on the authenticated users profile', function () {
    $this->post(route('alumkit.profile.careers.store'), [
        'job_title' => 'Developer',
        'company' => 'Acme',
        'employment_type' => 'full_time',
        'industry' => 'Software',
        'location' => 'Dhaka',
        'start_year' => 2020,
        'start_month' => 1,
        'is_current' => 1,
    ])
        ->assertRedirect(route('alumkit.profile').'#career')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('careers', [
        'profile_id' => $this->user->profile->id,
        'job_title' => 'Developer',
        'company' => 'Acme',
        'employment_type' => 'full_time',
        'is_current' => 1,
    ]);
});

it('validates required career fields on store', function () {
    $this->post(route('alumkit.profile.careers.store'), [
        'job_title' => '',
        'company' => '',
        'employment_type' => '',
    ])
        ->assertSessionHasErrors(['job_title', 'company', 'employment_type']);
});

it('updates a career record on the authenticated users profile', function () {
    $career = $this->user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);

    $this->put(route('alumkit.profile.careers.update', $career), [
        'job_title' => 'Senior Developer',
        'company' => 'Initech',
        'employment_type' => 'full_time',
        'start_year' => 2021,
    ])
        ->assertRedirect(route('alumkit.profile').'#career')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('careers', [
        'id' => $career->id,
        'profile_id' => $this->user->profile->id,
        'job_title' => 'Senior Developer',
        'company' => 'Initech',
    ]);
});

it('cannot update another users career record', function () {
    $other = User::factory()->create();
    $other->profile()->create();
    $otherCareer = $other->careers()->create(['job_title' => 'Designer', 'company' => 'Globex', 'employment_type' => 'contract', 'start_year' => 2018]);

    $this->put(route('alumkit.profile.careers.update', $otherCareer), [
        'job_title' => 'Developer',
        'company' => 'Acme',
        'employment_type' => 'full_time',
        'start_year' => 2020,
    ])
        ->assertNotFound();
});

it('cannot delete another users career record', function () {
    $other = User::factory()->create();
    $other->profile()->create();
    $otherCareer = $other->careers()->create(['job_title' => 'Designer', 'company' => 'Globex', 'employment_type' => 'contract', 'start_year' => 2018]);

    $this->delete(route('alumkit.profile.careers.destroy', $otherCareer))
        ->assertNotFound();

    $this->assertDatabaseHas('careers', ['id' => $otherCareer->id]);
});

it('deletes a career record on the authenticated users profile', function () {
    $career = $this->user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);

    $this->delete(route('alumkit.profile.careers.destroy', $career))
        ->assertRedirect(route('alumkit.profile').'#career')
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('careers', ['id' => $career->id]);
});

it('cannot open the edit form for another users career record', function () {
    $other = User::factory()->create();
    $other->profile()->create();
    $otherCareer = $other->careers()->create(['job_title' => 'Designer', 'company' => 'Globex', 'employment_type' => 'contract', 'start_year' => 2018]);

    $this->get(route('alumkit.profile.careers.edit', $otherCareer))
        ->assertNotFound();
});

it('validates career fields on update', function () {
    $career = $this->user->careers()->create(['job_title' => 'Developer', 'company' => 'Acme', 'employment_type' => 'full_time', 'start_year' => 2020]);

    $this->put(route('alumkit.profile.careers.update', $career), [
        'job_title' => '',
        'company' => '',
        'employment_type' => '',
    ])->assertSessionHasErrors(['job_title', 'company', 'employment_type']);
});

it('requires start_year on store', function () {
    $this->post(route('alumkit.profile.careers.store'), [
        'job_title' => 'Developer',
        'company' => 'Acme',
        'employment_type' => 'full_time',
    ])->assertSessionHasErrors(['start_year']);
});

it('rejects an end_year before start_year', function () {
    $this->post(route('alumkit.profile.careers.store'), [
        'job_title' => 'Developer',
        'company' => 'Acme',
        'employment_type' => 'full_time',
        'start_year' => 2020,
        'end_year' => 2015,
    ])->assertSessionHasErrors(['end_year']);
});

it('redirects guests away from the career routes', function () {
    Auth::logout();

    $this->get(route('alumkit.profile.careers.create'))
        ->assertRedirect(route('login'));
});
