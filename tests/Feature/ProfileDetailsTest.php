<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create();
    $this->user->profile()->create();
});

it('requires authentication', function () {
    $this->put(route('alumkit.profile.details.update'), [])
        ->assertRedirect(route('login'));
});

it('stores all profile details', function () {
    $this->actingAs($this->user)
        ->put(route('alumkit.profile.details.update'), [
            'date_of_birth' => '1990-05-15',
            'gender' => 'male',
            'blood_group' => 'O+',
            'present_address' => '123 Main St',
            'permanent_address' => '456 Elm St',
            'website' => 'https://example.com',
            'social_links' => [
                'facebook' => 'https://facebook.com/foo',
                'linkedin' => 'https://linkedin.com/in/foo',
            ],
            'emergency_contact' => [
                'name' => 'Jane Doe',
                'phone' => '555-0100',
                'relation' => 'Spouse',
            ],
        ])
        ->assertRedirect(route('alumkit.profile'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('profiles', [
        'id' => $this->user->profile->id,
        'gender' => 'male',
        'website' => 'https://example.com',
        'present_address' => '123 Main St',
        'permanent_address' => '456 Elm St',
        'social_links' => json_encode(['facebook' => 'https://facebook.com/foo', 'linkedin' => 'https://linkedin.com/in/foo']),
        'emergency_contact' => json_encode(['name' => 'Jane Doe', 'phone' => '555-0100', 'relation' => 'Spouse']),
    ]);
});

it('rejects an invalid gender and blood group', function () {
    $this->actingAs($this->user)
        ->put(route('alumkit.profile.details.update'), [
            'gender' => 'x',
            'blood_group' => 'x',
        ])
        ->assertSessionHasErrors(['gender', 'blood_group']);
});

it('rejects an invalid website and future date of birth', function () {
    $this->actingAs($this->user)
        ->put(route('alumkit.profile.details.update'), [
            'website' => 'nope',
            'date_of_birth' => now()->addDay()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors(['website', 'date_of_birth']);
});

it('uploads and serves the profile photo', function () {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->put(route('alumkit.profile.details.update'), [
            'photo' => UploadedFile::fake()->image('me.jpg'),
        ])
        ->assertRedirect(route('alumkit.profile'));

    $fresh = $this->user->profile->fresh();

    expect($fresh->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($fresh->photo_path);

    $this->get(route('alumkit.profile.photo.show', basename($fresh->photo_path)))->assertOk();
});

it('deletes the old photo when replaced', function () {
    Storage::fake('public');

    Storage::disk('public')->put('profile-photos/old.jpg', 'x');
    $this->user->profile->update(['photo_path' => 'profile-photos/old.jpg']);

    $this->actingAs($this->user)
        ->put(route('alumkit.profile.details.update'), [
            'photo' => UploadedFile::fake()->image('new.jpg'),
        ])
        ->assertRedirect(route('alumkit.profile'));

    $fresh = $this->user->profile->fresh();

    Storage::disk('public')->assertMissing('profile-photos/old.jpg');
    Storage::disk('public')->assertExists($fresh->photo_path);
});

it('renders the profile details form', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.profile'))
        ->assertOk()
        ->assertSee(__('alumkit::profile.details'));
});

it('stores profile details during profile completion', function () {
    $this->user->profile()->delete();

    $this->actingAs($this->user)
        ->post(route('alumkit.profile.complete.store'), [
            'educations' => [
                ['level' => 'masters', 'institution' => 'MIT'],
            ],
            'gender' => 'female',
            'website' => 'https://example.org',
            'date_of_birth' => '1990-05-15',
        ])
        ->assertRedirect(route('alumkit.dashboard'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('profiles', [
        'id' => $this->user->profile->id,
        'gender' => 'female',
        'website' => 'https://example.org',
    ]);

    expect($this->user->profile->fresh()->date_of_birth?->format('Y-m-d'))->toBe('1990-05-15');

    $this->assertDatabaseHas('educations', [
        'profile_id' => $this->user->profile->id,
        'level' => 'masters',
        'institution' => 'MIT',
    ]);
});

it('validates profile details during profile completion', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.profile.complete.store'), [
            'educations' => [
                ['level' => 'masters', 'institution' => 'MIT'],
            ],
            'gender' => 'x',
        ])
        ->assertSessionHasErrors(['gender']);
});
