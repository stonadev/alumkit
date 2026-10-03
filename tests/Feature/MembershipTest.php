<?php

declare(strict_types=1);

use Alumkit\Alumkit\Enums\MembershipStatus;
use Alumkit\Alumkit\Models\Membership;
use Alumkit\Alumkit\Models\MembershipPayment;
use Alumkit\Alumkit\Models\MembershipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->approved()->withProfile()->create();

    $this->plan = MembershipPlan::create([
        'name' => 'Gold',
        'price' => '100.00',
        'duration_days' => 30,
        'is_active' => true,
        'sort_order' => 0,
    ]);

    Permission::findOrCreate('manage memberships');
    $this->staff = User::factory()->approved()->withProfile()->create();
    $this->staff->givePermissionTo('manage memberships');
});

function seedPendingPayment(User $user, MembershipPlan $plan, array $overrides = []): MembershipPayment
{
    return MembershipPayment::create(array_merge([
        'user_id' => $user->id,
        'membership_plan_id' => $plan->id,
        'amount' => '100.00',
        'method' => 'cash',
        'paid_at' => now()->toDateString(),
        'status' => 'pending',
    ], $overrides));
}

it('creates the membership with a computed end date on approval', function () {
    $payment = seedPendingPayment($this->user, $this->plan);

    $this->actingAs($this->staff)
        ->post(route('alumkit.payments.approve', $payment))
        ->assertRedirect();

    $membership = $this->user->memberships()->first();

    expect($membership->status)->toBe('active')
        ->and($membership->ends_at?->toDateString())->toBe(now()->addDays(30)->toDateString());
});

it('extends from the existing end date on renewal', function () {
    $payment = seedPendingPayment($this->user, $this->plan);

    $this->actingAs($this->staff)
        ->post(route('alumkit.payments.approve', $payment))
        ->assertRedirect();

    $second = seedPendingPayment($this->user, $this->plan);

    $this->actingAs($this->staff)
        ->post(route('alumkit.payments.approve', $second))
        ->assertRedirect();

    expect($this->user->memberships()->count())->toBe(1)
        ->and($this->user->memberships()->first()->ends_at?->toDateString())
        ->toBe(now()->addDays(60)->toDateString());
});

it('switches the plan while preserving remaining time', function () {
    $payment = seedPendingPayment($this->user, $this->plan);

    $this->actingAs($this->staff)
        ->post(route('alumkit.payments.approve', $payment))
        ->assertRedirect();

    $yearly = MembershipPlan::create([
        'name' => 'Yearly',
        'price' => '900.00',
        'duration_days' => 360,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $second = seedPendingPayment($this->user, $yearly, ['amount' => '900.00']);

    $this->actingAs($this->staff)
        ->post(route('alumkit.payments.approve', $second))
        ->assertRedirect();

    $membership = $this->user->memberships()->first();

    expect($this->user->memberships()->count())->toBe(1)
        ->and($membership->membership_plan_id)->toBe($yearly->id)
        ->and($membership->ends_at?->toDateString())->toBe(now()->addDays(30)->addDays(360)->toDateString());
});

it('never shortens a lifetime membership', function () {
    $lifetime = MembershipPlan::create([
        'name' => 'Lifetime',
        'price' => '2500.00',
        'is_lifetime' => true,
        'is_active' => true,
        'sort_order' => 2,
    ]);

    $payment = seedPendingPayment($this->user, $lifetime, ['amount' => '2500.00']);

    $this->actingAs($this->staff)
        ->post(route('alumkit.payments.approve', $payment))
        ->assertRedirect();

    $second = seedPendingPayment($this->user, $this->plan);

    $this->actingAs($this->staff)
        ->post(route('alumkit.payments.approve', $second))
        ->assertRedirect();

    $membership = $this->user->memberships()->first();

    expect($this->user->memberships()->count())->toBe(1)
        ->and($membership->membership_plan_id)->toBe($lifetime->id)
        ->and($membership->ends_at)->toBeNull();
});

it('keeps only one active membership after repeated approvals', function () {
    foreach (range(1, 3) as $i) {
        $payment = seedPendingPayment($this->user, $this->plan, ['paid_at' => now()->toDateString()]);

        $this->actingAs($this->staff)
            ->post(route('alumkit.payments.approve', $payment))
            ->assertRedirect();
    }

    expect($this->user->memberships()->where('status', 'active')->count())->toBe(1);
});

it('derives expired on read for a lapsed row', function () {
    $payment = seedPendingPayment($this->user, $this->plan, [
        'paid_at' => now()->subDays(60)->toDateString(),
    ]);

    $this->actingAs($this->staff)
        ->post(route('alumkit.payments.approve', $payment))
        ->assertRedirect();

    // Backdate the membership so it has lapsed but the sweep has not run.
    Membership::where('user_id', $this->user->id)->update([
        'ends_at' => now()->subDay(),
    ]);

    $membership = $this->user->memberships()->first();

    expect($membership->effectiveStatus())->toBe(MembershipStatus::Expired)
        ->and($membership->isActive())->toBeFalse();
});

it('cancels an active membership', function () {
    $payment = seedPendingPayment($this->user, $this->plan);

    $this->actingAs($this->staff)
        ->post(route('alumkit.payments.approve', $payment))
        ->assertRedirect();

    $membership = $this->user->memberships()->first();

    $this->actingAs($this->staff)
        ->post(route('alumkit.memberships.cancel', $membership))
        ->assertRedirect();

    $this->assertDatabaseHas('memberships', ['id' => $membership->id, 'status' => 'cancelled']);
});

it('requires the end date to be after the start date', function () {
    $payment = seedPendingPayment($this->user, $this->plan);

    $this->actingAs($this->staff)
        ->post(route('alumkit.payments.approve', $payment))
        ->assertRedirect();

    $membership = $this->user->memberships()->first();

    $this->actingAs($this->staff)
        ->put(route('alumkit.memberships.update', $membership), [
            'ends_at' => now()->subYear()->toDateString(),
            'notes' => 'test',
        ])
        ->assertSessionHasErrors('ends_at');
});
