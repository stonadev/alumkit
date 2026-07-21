<?php

declare(strict_types=1);

use Alumkit\Alumkit\Alumkit;

it('resolves the singleton', function () {
    expect(app(Alumkit::class))->toBeInstanceOf(Alumkit::class);
});

it('returns the same instance from the container', function () {
    expect(app(Alumkit::class))->toBe(app(Alumkit::class));
});

it('merges the package config', function () {
    expect(config('alumkit.placeholder'))->toBe('default');
});

it('loads the package translations', function () {
    expect(trans('alumkit::messages.placeholder'))->toBe('Alumkit placeholder translation.');
});

it('loads the package views', function () {
    expect(view()->exists('alumkit::placeholder'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('alumkit:placeholder')
        ->expectsOutputToContain('Alumkit placeholder command executed.')
        ->assertSuccessful();
});
