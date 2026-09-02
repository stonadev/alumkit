<?php

declare(strict_types=1);

use Alumkit\Alumkit\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->approved()->create();
    $this->user->profile()->create();

    Permission::findOrCreate('manage pages');
    $this->user->givePermissionTo('manage pages');
});

it('lists registered globals', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.globals.index'))
        ->assertOk()
        ->assertSee('homepage')
        ->assertSee('contact');
});

it('renders a global edit screen from its schema', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.globals.edit', 'homepage'))
        ->assertOk()
        ->assertSee('Hero heading')
        ->assertSee('Welcome text');
});

it('404s for an unregistered global key', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.globals.edit', 'nope'))
        ->assertNotFound();
});

it('updates global content', function () {
    $this->actingAs($this->user)
        ->put(route('alumkit.globals.update', 'homepage'), [
            'fields' => [
                'hero_heading' => 'New Hero',
                'welcome_text' => 'New welcome',
            ],
        ])
        ->assertRedirect(route('alumkit.globals.edit', 'homepage'));

    $global = Content::forGlobal('homepage')->first();

    expect($global->fields['hero_heading'])->toBe('New Hero');
    expect($global->fields['welcome_text'])->toBe('New welcome');
});

it('keeps stored values not present in the request', function () {
    Content::updateOrCreate(['owner' => 'global:homepage', 'type' => 'global'], [
        'fields' => [
            'hero_heading' => 'Old Hero',
            'welcome_text' => '',
            'logo' => 'content-images/logo.png',
        ],
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.globals.update', 'homepage'), [
            'fields' => [
                'hero_heading' => 'New Hero',
                'welcome_text' => '',
            ],
        ])
        ->assertRedirect(route('alumkit.globals.edit', 'homepage'));

    $global = Content::forGlobal('homepage')->first();

    expect($global->fields['hero_heading'])->toBe('New Hero');
    expect($global->fields['logo'])->toBe('content-images/logo.png');
});
