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

it('ships the utilities used by the package views', function () {
    $css = file_get_contents(__DIR__.'/../../public/alumkit.css');

    expect($css)->toContain('.p-8')->toContain('.min-h-screen')->toContain('.text-sm');
});
