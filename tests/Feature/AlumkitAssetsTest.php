<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('serves the compiled package stylesheet', function () {
    $this->get('alumkit/style/alumkit.css')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/css; charset=utf-8');
});

it('serves the compiled editor bundle', function () {
    $this->get('alumkit/style/alumkit-editor.js')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/javascript');
});

it('serves the compiled editor stylesheet', function () {
    $this->get('alumkit/style/alumkit-editor.css')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/css; charset=utf-8');
});

it('forces revalidation so composer update ships fresh styles', function () {
    $cacheControl = $this->get('alumkit/style/alumkit.css')
        ->headers->get('Cache-Control');

    expect($cacheControl)->toContain('no-cache');
});

it('serves a 304 when the asset is unchanged', function () {
    $response = $this->get('alumkit/style/alumkit.css');

    $this->get('alumkit/style/alumkit.css', [
        'If-Modified-Since' => $response->headers->get('Last-Modified'),
    ])->assertStatus(304);
});

it('rejects unknown asset names', function () {
    $this->get('alumkit/style/alumkit-admin.css')->assertNotFound();

    $this->get('alumkit/style/../../composer.json')->assertNotFound();
});

it('ships the utilities used by the package views', function () {
    $css = file_get_contents(__DIR__.'/../../public/alumkit.css');

    expect($css)->toContain('.p-8')->toContain('.min-h-screen')->toContain('.text-sm');
});
