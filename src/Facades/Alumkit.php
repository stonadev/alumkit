<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Alumkit\Alumkit\Alumkit
 */
class Alumkit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Alumkit\Alumkit\Alumkit::class;
    }
}
