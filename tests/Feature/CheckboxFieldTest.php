<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;

beforeEach(function () {
    // Blade::render skips the web middleware that shares $errors; @error needs it.
    view()->share('errors', new ViewErrorBag);
});

it('renders hidden zero input, checkbox, and label', function () {
    $html = Blade::render('<x-alumkit::checkbox name="published" label="Publish" />');

    expect($html)
        ->toContain('<input type="hidden" name="published" value="0">')
        ->toContain('<input type="checkbox" name="published" value="1"')
        ->toContain('Publish');
});

it('renders checked state only when checked', function () {
    $checked = Blade::render('<x-alumkit::checkbox name="published" :checked="true" />');
    $unchecked = Blade::render('<x-alumkit::checkbox name="published" :checked="false" />');

    expect($checked)->toContain('checked');
    expect($unchecked)->not->toContain('checked');
});

it('passes extra attributes through to the checkbox input', function () {
    $html = Blade::render('<x-alumkit::checkbox name="is_current" x-model="is_current" />');

    expect($html)->toContain('x-model="is_current"');
});
