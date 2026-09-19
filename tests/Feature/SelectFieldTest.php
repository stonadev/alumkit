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

it('renders year options from current year plus five down to 1991', function () {
    $html = Blade::render('<x-alumkit::year-select name="start_year" value="2020" />');

    expect($html)
        ->toContain('name="start_year"')
        ->toContain('<option value="2020" selected')
        ->toContain('<option value="1991"')
        ->toContain('<option value="'.date('Y').'"')
        ->toContain('<option value="'.(date('Y') + 5).'"');
});

it('renders month options 1 through 12 with month-name labels', function () {
    $html = Blade::render('<x-alumkit::month-select name="start_month" value="6" />');

    expect($html)
        ->toContain('name="start_month"')
        ->toContain('<option value="6" selected')
        ->toContain('>January</option>')
        ->toContain('>December</option>');
});
