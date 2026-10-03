<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Tests\Feature\Toggled;

use Illuminate\Support\Facades\Route;

class MembershipsOnlyDisabledTest extends FeatureToggleDisabledTestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('alumkit.features.posts', true);
        $app['config']->set('alumkit.features.committee', true);
        $app['config']->set('alumkit.features.memberships', false);
    }

    public function test_membership_routes_absent_when_only_memberships_disabled(): void
    {
        $this->assertFalse(Route::has('alumkit.membership.show'));
        $this->assertFalse(Route::has('alumkit.membership.plans'));
        $this->assertFalse(Route::has('alumkit.membership.payments.create'));
        $this->assertFalse(Route::has('alumkit.plans.index'));
        $this->assertFalse(Route::has('alumkit.memberships.index'));
        $this->assertFalse(Route::has('alumkit.payments.index'));
    }

    public function test_other_routes_still_registered_when_only_memberships_disabled(): void
    {
        $this->assertTrue(Route::has('alumkit.posts.index'));
        $this->assertTrue(Route::has('alumkit.committee.index'));
        $this->assertTrue(Route::has('alumkit.positions.index'));
    }
}
