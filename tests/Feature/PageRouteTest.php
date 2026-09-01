<?php

declare(strict_types=1);

use Alumkit\Alumkit\Facades\Alumkit;
use Alumkit\Alumkit\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
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

    // The workbench route and workbench:: view namespace are only wired by
    // the served workbench app; register both so the package gate runs.
    Route::get('/about', Alumkit::pageRoute('about'));
    view()->addNamespace('workbench', dirname(__DIR__, 2).'/workbench/resources/views');
});

it('renders a published page to guests', function () {
    $this->get('/about')
        ->assertOk()
        ->assertSee('About Alumkit');
});

it('404s an unpublished page for guests', function () {
    Page::where('slug', 'about')->update(['is_published' => false]);

    $this->get('/about')->assertNotFound();
});

it('renders an unpublished page preview for manage-pages users', function () {
    Page::where('slug', 'about')->update(['is_published' => false]);

    $this->actingAs($this->user)
        ->get('/about')
        ->assertOk()
        ->assertSee('About Alumkit');
});

it('404s a page without a registered schema view', function () {
    Page::create([
        'title' => 'No View',
        'slug' => 'no-view',
        'is_published' => true,
    ]);

    Route::get('/no-view', Alumkit::pageRoute('no-view'));

    $this->get('/no-view')->assertNotFound();
});
