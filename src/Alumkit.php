<?php

declare(strict_types=1);

namespace Alumkit\Alumkit;

class Alumkit
{
    /**
     * Package-defined permissions. Always seeded; cannot be removed by the consumer app.
     */
    public const PERMISSIONS = [
        'manage roles',
        'manage permissions',
        'manage members',
        'manage educations',
        'view dashboard',
    ];
}
