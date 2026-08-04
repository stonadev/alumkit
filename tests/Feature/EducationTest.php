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

it('creates a user with educations during registration', function () {
    $this->post(route('register'), [
        'name' => 'Education User',
        'email' => 'edu@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'educations' => [
            [
                'level' => 'masters',
                'institution' => 'MIT',
                'subject' => 'Computer Science',
                'start_year' => '2020',
                'start_month' => '9',
                'end_year' => '2022',
                'end_month' => '6',
            ],
            [
                'level' => 'phd',
                'institution' => 'Stanford',
                'subject' => 'AI',
                'start_year' => '2022',
                'start_month' => '9',
            ],
        ],
    ])->assertSessionHasNoErrors()
        ->assertRedirect();

    $user = User::where('email', 'edu@example.com')->first();
    expect($user->educations)->toHaveCount(2);
    expect($user->educations->first()->level)->toBe('masters');
    expect($user->educations->first()->institution)->toBe('MIT');
    expect($user->educations->last()->level)->toBe('phd');
    expect($user->educations->last()->end_year)->toBeNull();
});

it('requires at least one education during registration', function () {
    $this->post(route('register'), [
        'name' => 'No Edu User',
        'email' => 'noedu@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors(['educations']);
});

it('validates education level during registration', function () {
    $this->post(route('register'), [
        'name' => 'Invalid Edu User',
        'email' => 'invalid@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'educations' => [
            [
                'level' => 'invalid_level',
                'institution' => 'MIT',
            ],
        ],
    ])->assertSessionHasErrors(['educations.0.level']);
});

it('validates institution required during registration when educations present', function () {
    $this->post(route('register'), [
        'name' => 'No Inst User',
        'email' => 'noinst@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'educations' => [
            [
                'level' => 'masters',
                'institution' => '',
            ],
        ],
    ])->assertSessionHasErrors(['educations.0.institution']);
});
