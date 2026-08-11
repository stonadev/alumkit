<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('serves the compiled package stylesheet', function () {
    $this->get('alumkit/style/alumkit.css')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/css; charset=utf-8');
});

it('ships the utilities used by the package views', function () {
    $css = file_get_contents(__DIR__.'/../../public/alumkit.css');

    expect($css)->toContain('.p-8')->toContain('.min-h-screen')->toContain('.text-sm');
});
