<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Tests\Feature\Toggled;

use Alumkit\Alumkit\Tests\TestCase;

class FeatureToggleDisabledTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('alumkit.features.posts', false);
        $app['config']->set('alumkit.features.committee', false);
    }
}
