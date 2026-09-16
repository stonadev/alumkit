<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Tests\Feature\Toggled;

use Alumkit\Alumkit\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class FeatureTogglePostsOnlyDisabledTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('alumkit.features.posts', false);
        $app['config']->set('alumkit.features.committee', true);
    }

    public function test_posts_routes_unregistered_when_only_posts_disabled(): void
    {
        $this->assertFalse(Route::has('alumkit.posts.index'));
        $this->assertFalse(Route::has('alumkit.posts.thumbnail'));
    }

    public function test_committee_routes_still_registered_when_only_posts_disabled(): void
    {
        $this->assertTrue(Route::has('alumkit.committee.index'));
        $this->assertTrue(Route::has('alumkit.committee.photo'));
        $this->assertTrue(Route::has('alumkit.positions.index'));
    }
}
