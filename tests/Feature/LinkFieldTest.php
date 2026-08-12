<?php

declare(strict_types=1);

use Alumkit\Alumkit\Http\Livewire\LinkField;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

beforeEach(function () {
    Route::get('events', fn () => 'ok')->name('events.index');
    Route::get('posts/{post}', fn () => 'ok')->name('posts.edit');
});

it('renders via the anonymous component', function () {
    $html = Blade::render('<x-alumkit::link-field name="website" />');

    expect($html)
        ->toContain('name="website[label]"')
        ->toContain('name="website[url]"')
        ->toContain('alumkit-link-field-modal-');
});

it('suggests named routes without required params', function () {
    Livewire::test(LinkField::class, ['name' => 'website'])
        ->set('url', 'events')
        ->assertSee('events.index')
        ->assertDontSee('posts.edit');
});

it('picking a route sets the URL and auto-fills the label', function () {
    Livewire::test(LinkField::class, ['name' => 'website'])
        ->set('url', 'events')
        ->call('pickRoute', 'events.index')
        ->assertSet('url', url('/events'))
        ->assertSet('label', 'Events Index')
        ->assertSet('suggestions', []);
});

it('saves a custom URL and closes the modal', function () {
    Livewire::test(LinkField::class, ['name' => 'website'])
        ->set('label', 'My site')
        ->set('url', 'https://example.com')
        ->set('showModal', true)
        ->call('save')
        ->assertSet('showModal', false)
        ->assertSet('url', 'https://example.com')
        ->assertSeeHtml('name="website[url]" value="https://example.com"');
});

it('empty URL save is a no-op', function () {
    Livewire::test(LinkField::class, ['name' => 'website'])
        ->call('save')
        ->assertSet('showModal', false)
        ->assertSet('url', null);
});

it('clear empties both values', function () {
    Livewire::test(LinkField::class, ['name' => 'website'])
        ->set('label', 'My site')
        ->set('url', 'https://example.com')
        ->call('clear')
        ->assertSet('label', null)
        ->assertSet('url', null);
});

it('gives each instance a distinct modal id', function () {
    $html = Blade::render('<x-alumkit::link-field name="a" /><x-alumkit::link-field name="b" />');

    preg_match_all('/id="alumkit-link-field-modal-[^"]+"/', $html, $matches);

    expect(count(array_unique($matches[0])))->toBe(2);
});

it('shows the no-matches hint for unknown URLs', function () {
    Livewire::test(LinkField::class, ['name' => 'website'])
        ->set('url', 'zzz-nothing')
        ->assertDontSee('events.index')
        ->assertSee('No matching routes');
});
