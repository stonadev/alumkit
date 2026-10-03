<?php

declare(strict_types=1);

use Alumkit\Alumkit\Facades\Alumkit;
use Alumkit\Alumkit\Models\MembershipPaymentMethod;
use Alumkit\Alumkit\Models\MembershipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        'features' => ['directory_access' => 'true', 'event_discount' => '10'],
    ]);
});

it('returns active plans', function () {
    MembershipPlan::create([
        'name' => 'Hidden',
        'price' => '50.00',
        'duration_days' => 30,
        'is_active' => false,
        'sort_order' => 1,
    ]);

    $names = Alumkit::activePlans()->pluck('name');

    expect($names)->toContain('Gold')->not->toContain('Hidden');
});

it('returns the membership for a user', function () {
    $this->user->memberships()->create([
        'membership_plan_id' => $this->plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    expect(Alumkit::membershipFor($this->user))->not->toBeNull()
        ->and(Alumkit::membershipFor($this->user))->plan->name->toBe('Gold');
});

it('answers hasActiveMembership', function () {
    expect(Alumkit::hasActiveMembership($this->user))->toBeFalse();

    $this->user->memberships()->create([
        'membership_plan_id' => $this->plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    expect(Alumkit::hasActiveMembership($this->user))->toBeTrue();
});

it('formats money', function () {
    expect(Alumkit::formatMoney(1500))->toBe('BDT 1,500.00')
        ->and(Alumkit::formatMoney('99.5'))->toBe('BDT 99.50');
});

it('lists only dashboard-managed payment methods', function () {
    MembershipPaymentMethod::create([
        'type' => 'bank_transfer',
        'is_active' => true,
    ]);

    $methods = Alumkit::paymentMethods();

    expect($methods)->toHaveKey('bank_transfer')
        ->and($methods)->not->toHaveKey('free');
});

it('reads membership features with truthiness', function () {
    expect($this->user->hasActiveMembership())->toBeFalse()
        ->and($this->user->hasMembershipFeature('directory_access'))->toBeFalse();

    $this->user->memberships()->create([
        'membership_plan_id' => $this->plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    expect($this->user->membershipFeature('directory_access'))->toBe('true')
        ->and($this->user->hasMembershipFeature('directory_access'))->toBeTrue()
        ->and($this->user->hasMembershipFeature('event_discount'))->toBeFalse()
        ->and($this->user->membershipFeature('missing'))->toBeNull();
});
