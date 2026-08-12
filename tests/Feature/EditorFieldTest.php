<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders the editor field with name, label, and asset tags', function () {
    $html = Blade::render('<x-alumkit::editor-field name="body" label="Body" />');

    expect($html)
        ->toContain('name="body"')
        ->toContain('data-alumkit-editor')
        ->toContain('data-upload-url=')
        ->toContain('Body')
        ->toContain('alumkit/style/alumkit-editor.js')
        ->toContain('alumkit/style/alumkit-editor.css');
});

it('carries the initial value in the hidden input and data attribute', function () {
    $html = Blade::render('<x-alumkit::editor-field name="a" value=\'{"blocks":[],"version":"2.30.0"}\' />');

    expect($html)
        ->toContain('data-value="{&quot;blocks&quot;:[],&quot;version&quot;:&quot;2.30.0&quot;}"')
        ->toContain('name="a" value="{&quot;blocks&quot;:[],&quot;version&quot;:&quot;2.30.0&quot;}"');
});

it('renders one editor holder per instance', function () {
    $html = Blade::render('<x-alumkit::editor-field name="a" /><x-alumkit::editor-field name="b" />');

    expect(substr_count($html, 'data-alumkit-editor'))->toBe(2);
});
