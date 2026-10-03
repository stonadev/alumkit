<?php

declare(strict_types=1);

use Alumkit\Alumkit\Models\MembershipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->approved()->withProfile()->create();
});

function gatedPlan(array $features): MembershipPlan
{
    return MembershipPlan::create([
        'name' => 'Gated',
        'price' => '100.00',
        'duration_days' => 30,
        'is_active' => true,
        'sort_order' => 0,
        'features' => $features,
    ]);
}

it('blocks a non-member from the member directory when memberships are enabled', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.users.index'))
        ->assertRedirect(route('alumkit.membership.show'));
});

it('lets membership staff bypass the member-directory gate', function () {
    Permission::findOrCreate('manage memberships');
    $staff = User::factory()->approved()->withProfile()->create();
    $staff->givePermissionTo('manage memberships');

    $this->actingAs($staff)
        ->get(route('alumkit.users.index'))
        ->assertOk();
});

it('lets a member with the members feature reach the member directory', function () {
    $plan = gatedPlan(['members' => '1']);
    $this->user->memberships()->create([
        'membership_plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.users.index'))
        ->assertOk();
});

it('keeps the member directory open to approved users when memberships are disabled', function () {
    $this->app['config']->set('alumkit.features.memberships', false);

    $this->actingAs($this->user)
        ->get(route('alumkit.users.index'))
        ->assertOk();
});

it('blocks a non-member from posts when memberships are enabled', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.posts.index'))
        ->assertRedirect(route('alumkit.membership.show'));
});

it('lets a member with the posts feature reach posts', function () {
    $plan = gatedPlan(['posts' => '1']);
    $this->user->memberships()->create([
        'membership_plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.posts.index'))
        ->assertOk();
});

it('stores the gated-feature toggles on the plan', function () {
    Permission::findOrCreate('manage membership plans');
    $admin = User::factory()->approved()->withProfile()->create();
    $admin->givePermissionTo('manage membership plans');

    $this->actingAs($admin)
        ->post(route('alumkit.plans.store'), [
            'name' => 'Gold',
            'price' => '100.00',
            'term_days' => 30,
            'feature_members' => '1',
        ])
        ->assertRedirect(route('alumkit.plans.index'));

    $plan = MembershipPlan::where('name', 'Gold')->first();

    expect($plan->features)->toBe(['members' => '1']);
});
