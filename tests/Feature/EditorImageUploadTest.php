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

    $this->user = User::factory()->approved()->create();
});

it('stores an uploaded image and serves it through the package route', function () {
    Storage::fake('public');

    $response = $this->actingAs($this->user)
        ->post(route('alumkit.editor.image'), ['file' => UploadedFile::fake()->image('photo.jpg')])
        ->assertOk()
        ->assertJson(['success' => 1]);

    $url = $response->json('file.url');

    $this->get($url)->assertOk();

    Storage::disk('public')->assertExists('editor-images/'.basename($url));
});

it('rejects non-image uploads', function () {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->post(route('alumkit.editor.image'), ['file' => UploadedFile::fake()->create('doc.txt', 1)], ['Accept' => 'application/json'])
        ->assertStatus(422);
});
