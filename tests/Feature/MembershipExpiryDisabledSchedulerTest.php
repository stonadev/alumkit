<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Tests\Feature;

use Alumkit\Alumkit\Tests\TestCase;
use Illuminate\Support\Facades\Schedule;

class MembershipExpiryDisabledSchedulerTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('alumkit.membership.expiry.enabled', false);
    }

    public function test_membership_expiry_is_not_scheduled_when_disabled(): void
    {
        $events = collect(Schedule::events());

        $registered = $events->contains(fn ($event) => str_contains((string) $event->description, 'alumkit:memberships:expire')
            || str_contains((string) $event->command, 'alumkit:memberships:expire'));

        $this->assertFalse($registered);
    }
}
