<?php

declare(strict_types=1);

use Alumkit\Alumkit\Events\MembershipExpired;
use Alumkit\Alumkit\Models\Membership;
use Alumkit\Alumkit\Models\MembershipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schedule;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->plan = MembershipPlan::create([
        'name' => 'Gold',
        'price' => '100.00',
        'duration_days' => 30,
        'is_active' => true,
        'sort_order' => 0,
    ]);
});

function makeMembership(MembershipPlan $plan, array $overrides = []): Membership
{
    $user = User::factory()->approved()->create();

    return Membership::create(array_merge([
        'user_id' => $user->id,
        'membership_plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now()->subDays(60),
        'ends_at' => now()->subDay(),
    ], $overrides));
}

it('flips only lapsed non-lifetime rows', function () {
    $lapsed = makeMembership($this->plan);
    $current = makeMembership($this->plan, ['starts_at' => now(), 'ends_at' => now()->addMonth()]);
    $lifetimePlan = MembershipPlan::create([
        'name' => 'Lifetime',
        'price' => '2500.00',
        'is_lifetime' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    $lifetime = makeMembership($lifetimePlan, ['ends_at' => null]);

    Event::fake([MembershipExpired::class]);

    $this->artisan('alumkit:memberships:expire')
        ->assertSuccessful();

    $this->assertDatabaseHas('memberships', ['id' => $lapsed->id, 'status' => 'expired']);
    $this->assertDatabaseHas('memberships', ['id' => $current->id, 'status' => 'active']);
    $this->assertDatabaseHas('memberships', ['id' => $lifetime->id, 'status' => 'active']);

    Event::assertDispatched(MembershipExpired::class, 1);
});

it('is idempotent', function () {
    makeMembership($this->plan);

    $this->artisan('alumkit:memberships:expire')->assertSuccessful();
    $this->artisan('alumkit:memberships:expire')->assertSuccessful();

    $this->assertSame(1, Membership::where('status', 'expired')->count());
});

it('registers the scheduler when enabled', function () {
    $events = collect(Schedule::events());

    expect($events->contains(fn ($event) => str_contains((string) $event->description, 'alumkit:memberships:expire')
        || str_contains((string) $event->command, 'alumkit:memberships:expire')))->toBeTrue();
});
