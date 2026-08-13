<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;

beforeEach(function () {
    // Blade::render skips the web middleware that shares $errors; @error needs it.
    view()->share('errors', new ViewErrorBag);
});

it('renders name, label, options, selected value, and required', function () {
    $html = Blade::render('<x-alumkit::select name="level" label="Level" :options="[\'honors\' => \'Honors\', \'phd\' => \'PhD\']" value="phd" required />');

    expect($html)
        ->toContain('name="level"')
        ->toContain('Level')
        ->toContain('>Honors</option>')
        ->toContain('>PhD</option>')
        ->toContain('<option value="phd" selected')
        ->toContain('required');
});

it('selects no option when value is omitted', function () {
    $html = Blade::render('<x-alumkit::select name="level" :options="[\'honors\' => \'Honors\', \'phd\' => \'PhD\']" />');

    expect($html)->not->toContain('selected');
});
