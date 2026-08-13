<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;

beforeEach(function () {
    // Blade::render skips the web middleware that shares $errors; @error needs it.
    view()->share('errors', new ViewErrorBag);
});

it('renders name, default rows, label, and value', function () {
    $html = Blade::render('<x-alumkit::textarea name="description" label="Description" value="Old text" />');

    expect($html)
        ->toContain('name="description"')
        ->toContain('rows="4"')
        ->toContain('Description')
        ->toContain('Old text');
});

it('honors a custom row count', function () {
    $html = Blade::render('<x-alumkit::textarea name="description" rows="6" />');

    expect($html)->toContain('rows="6"');
});
