<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Tests\Feature\Toggled;

use Alumkit\Alumkit\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class FeatureToggleCommitteeOnlyDisabledTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('alumkit.features.posts', true);
        $app['config']->set('alumkit.features.committee', false);
    }

    public function test_committee_routes_unregistered_when_only_committee_disabled(): void
    {
        $this->assertFalse(Route::has('alumkit.committee.index'));
        $this->assertFalse(Route::has('alumkit.committee.photo'));
        $this->assertFalse(Route::has('alumkit.positions.index'));
    }

    public function test_posts_routes_still_registered_when_only_committee_disabled(): void
    {
        $this->assertTrue(Route::has('alumkit.posts.index'));
        $this->assertTrue(Route::has('alumkit.posts.thumbnail'));
    }
}
