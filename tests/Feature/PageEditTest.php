<?php

declare(strict_types=1);

use Alumkit\Alumkit\Models\Content;
use Alumkit\Alumkit\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->approved()->create();
    $this->user->profile()->create();
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2018]);

    Permission::findOrCreate('manage pages');
    $this->user->givePermissionTo('manage pages');
});

it('renders the edit page with content and meta tabs', function () {
    $page = Page::where('slug', 'about')->first();

    $this->actingAs($this->user)
        ->get(route('alumkit.pages.edit', $page))
        ->assertOk()
        ->assertSee('href="#content"', false)
        ->assertSee('href="#meta"', false)
        ->assertSee('Page Details')
        ->assertSee('Heading')
        ->assertSee('+ Add Row')
        ->assertSee('data-alumkit-editor');
});

it('updates page details and content in a single request', function () {
    $page = Page::where('slug', 'about')->first();

    $this->actingAs($this->user)
        ->put(route('alumkit.pages.update', $page), [
            'title' => 'About Us',
            'slug' => 'about',
            'meta_title' => 'About the Alumni Network',
            'meta_description' => 'Learn about the alumni network.',
            'is_published' => 1,
            'fields' => [
                'heading' => 'New Heading',
                'body' => 'New body text',
                'members' => [
                    ['name' => 'Alice', 'role' => 'Admin'],
                ],
            ],
        ])
        ->assertRedirect(route('alumkit.pages.edit', $page));

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'title' => 'About Us',
        'is_published' => true,
    ]);

    $this->assertDatabaseHas('contents', [
        'owner' => 'page:about',
        'type' => 'hero',
    ]);

    $this->assertDatabaseHas('contents', [
        'owner' => 'page:about',
        'type' => 'team',
    ]);

    $hero = Content::where('owner', 'page:about')->where('type', 'hero')->first();
    expect($hero->fields['heading'])->toBe('New Heading');

    $team = Content::where('owner', 'page:about')->where('type', 'team')->first();
    expect($team->fields['members'])->toBe([
        ['name' => 'Alice', 'role' => 'Admin'],
    ]);
});

it('renders the edit page for pages without a registered schema', function () {
    $page = Page::create([
        'title' => 'No Schema',
        'slug' => 'no-schema',
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.pages.edit', $page))
        ->assertOk()
        ->assertSee('No content schema registered', false)
        ->assertDontSee('data-alumkit-editor');
});

it('keeps stored image and select values when saving without touching them', function () {
    $page = Page::where('slug', 'about')->first();

    Content::updateOrCreate(['owner' => 'page:about', 'type' => 'hero'], [
        'fields' => [
            'heading' => 'Original',
            'body' => '',
            'layout' => 'wide',
            'banner' => 'content-images/banner.jpg',
        ],
    ]);

    $this->actingAs($this->user)
        ->put(route('alumkit.pages.update', $page), [
            'title' => 'About Us',
            'slug' => 'about',
            'is_published' => 1,
            'fields' => [
                'heading' => 'Changed',
                'body' => '',
                'members' => [],
            ],
        ])
        ->assertRedirect(route('alumkit.pages.edit', $page));

    $hero = Content::where('owner', 'page:about')->where('type', 'hero')->first();

    expect($hero->fields['heading'])->toBe('Changed');
    expect($hero->fields['banner'])->toBe('content-images/banner.jpg');
    expect($hero->fields['layout'])->toBe('wide');
});

it('whitelists select values against the schema', function () {
    $page = Page::where('slug', 'about')->first();

    $this->actingAs($this->user)
        ->put(route('alumkit.pages.update', $page), [
            'title' => 'About Us',
            'slug' => 'about',
            'is_published' => 1,
            'fields' => [
                'heading' => 'About',
                'body' => '',
                'layout' => 'not-an-option',
                'members' => [],
            ],
        ])
        ->assertRedirect(route('alumkit.pages.edit', $page));

    $hero = Content::where('owner', 'page:about')->where('type', 'hero')->first();

    expect($hero->fields['layout'])->toBe('');
});

it('deletes page content when the page is deleted', function () {
    $page = Page::where('slug', 'about')->first();

    expect(Content::forPage('about')->count())->toBeGreaterThan(0);

    $page->delete();

    expect(Content::forPage('about')->count())->toBe(0);
});
