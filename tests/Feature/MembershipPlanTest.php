<?php

declare(strict_types=1);

use Alumkit\Alumkit\Models\MembershipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->approved()->withProfile()->create();

    Permission::findOrCreate('manage membership plans');
    $this->user->givePermissionTo('manage membership plans');
});

/** Authoring payload for the plan form (term_days / term_months / is_lifetime). */
function makePlan(array $overrides = []): array
{
    return array_merge([
        'name' => 'Gold',
        'price' => '100.00',
        'term_days' => 30,
        'is_lifetime' => false,
        'sort_order' => 0,
        'is_active' => true,
    ], $overrides);
}

/** Stored shape for direct model creation (a base day count + is_lifetime). */
function makeStoredPlan(array $overrides = []): array
{
    return array_merge([
        'name' => 'Gold',
        'price' => '100.00',
        'duration_days' => 30,
        'is_lifetime' => false,
        'sort_order' => 0,
        'is_active' => true,
    ], $overrides);
}

it('renders the plans index', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.plans.index'))
        ->assertOk();
});

it('renders the create plan form with term choices', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.plans.create'))
        ->assertOk()
        ->assertSee('name="term_type"', false)
        ->assertSee('value="lifetime"', false)
        ->assertDontSee('name="currency"', false);
});

it('renders the edit plan form for a stored plan', function () {
    $plan = MembershipPlan::create(makeStoredPlan(['duration_days' => 360]));

    $this->actingAs($this->user)
        ->get(route('alumkit.plans.edit', $plan))
        ->assertOk()
        ->assertSee('name="term_type"', false)
        ->assertDontSee('name="currency"', false);
});

it('denies the plans index without permission', function () {
    $other = User::factory()->approved()->withProfile()->create();

    $this->actingAs($other)
        ->get(route('alumkit.plans.index'))
        ->assertForbidden();
});

it('creates a plan with a day term', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.plans.store'), makePlan())
        ->assertRedirect(route('alumkit.plans.index'));

    $this->assertDatabaseHas('membership_plans', ['name' => 'Gold', 'duration_days' => 30]);
});

it('stores a month term as a base day count', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.plans.store'), makePlan(['term_days' => null, 'term_months' => 12]))
        ->assertRedirect(route('alumkit.plans.index'));

    $this->assertDatabaseHas('membership_plans', ['name' => 'Gold', 'duration_days' => 360]);
});

it('requires exactly one term', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.plans.store'), makePlan([
            'term_days' => null,
            'is_lifetime' => false,
        ]))
        ->assertSessionHasErrors('term_days');
});

it('rejects two terms set at once', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.plans.store'), makePlan(['term_months' => 12]))
        ->assertSessionHasErrors('term_days');
});

it('accepts a lifetime term', function () {
    $this->actingAs($this->user)
        ->post(route('alumkit.plans.store'), makePlan([
            'term_days' => null,
            'is_lifetime' => true,
        ]))
        ->assertRedirect(route('alumkit.plans.index'));

    $this->assertDatabaseHas('membership_plans', ['name' => 'Gold', 'is_lifetime' => true]);
});

it('updates a plan', function () {
    $plan = MembershipPlan::create(makeStoredPlan());

    $this->actingAs($this->user)
        ->put(route('alumkit.plans.update', $plan), makePlan(['price' => '150.00']))
        ->assertRedirect(route('alumkit.plans.index'));

    $this->assertDatabaseHas('membership_plans', ['id' => $plan->id, 'price' => '150.00']);
});

it('prevents deleting a plan in use', function () {
    $plan = MembershipPlan::create(makeStoredPlan());
    $member = User::factory()->approved()->create();
    $member->memberships()->create([
        'membership_plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $this->actingAs($this->user)
        ->delete(route('alumkit.plans.destroy', $plan))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('membership_plans', ['id' => $plan->id]);
});

it('deletes an unused plan', function () {
    $plan = MembershipPlan::create(makeStoredPlan());

    $this->actingAs($this->user)
        ->delete(route('alumkit.plans.destroy', $plan))
        ->assertRedirect(route('alumkit.plans.index'));

    $this->assertDatabaseMissing('membership_plans', ['id' => $plan->id]);
});

it('labels the term', function () {
    expect(MembershipPlan::create(makeStoredPlan())->termLabel())->toBe('30 days');
    expect(MembershipPlan::create(makeStoredPlan(['duration_days' => 360]))->termLabel())->toBe('12 months');
    expect(MembershipPlan::create(makeStoredPlan(['duration_days' => null, 'is_lifetime' => true]))->termLabel())->toBe('Lifetime');
});

it('computes the term end for all three shapes', function () {
    $start = Carbon::parse('2026-01-01');

    $days = MembershipPlan::create(makeStoredPlan());
    expect($days->termEndFrom($start)?->toDateString())->toBe('2026-01-31');

    $months = MembershipPlan::create(makeStoredPlan(['duration_days' => 90]));
    expect($months->termEndFrom($start)?->toDateString())->toBe('2026-04-01');

    $lifetime = MembershipPlan::create(makeStoredPlan(['duration_days' => null, 'is_lifetime' => true]));
    expect($lifetime->termEndFrom($start))->toBeNull();
});
