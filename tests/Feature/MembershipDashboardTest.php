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
    $this->user->educations()->create(['level' => 'masters', 'institution' => 'MIT', 'subject' => 'Computer Science', 'start_year' => 2015]);
});

it('shows the membership sidebar entry to active members', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee(__('alumkit::membership.membership'));
});

it('hides the membership sidebar entry when the feature is off', function () {
    config(['alumkit.features.memberships' => false]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertDontSee(__('alumkit::membership.membership'));
});

it('shows the membership status card', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee(__('alumkit::membership.no_membership'));
});

it('shows the permission-gated staff nav block', function () {
    Permission::findOrCreate('manage membership plans');
    Permission::findOrCreate('manage memberships');
    $this->user->givePermissionTo('manage membership plans');
    $this->user->givePermissionTo('manage memberships');

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee(__('alumkit::membership.manage_plans'))
        ->assertSee(__('alumkit::membership.payment_queue'));
});

it('hides the staff nav block without the permissions', function () {
    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertDontSee(__('alumkit::membership.manage_plans'))
        ->assertDontSee(__('alumkit::membership.payment_queue'));
});

it('shows the membership card on the member page for staff', function () {
    Permission::findOrCreate('manage memberships');
    $this->user->givePermissionTo('manage memberships');

    $subject = User::factory()->approved()->create();

    $this->actingAs($this->user)
        ->get(route('alumkit.users.show', $subject))
        ->assertOk()
        ->assertSee(__('alumkit::membership.current_membership'));
});

it('shows a paid membership on the dashboard card', function () {
    $plan = MembershipPlan::create([
        'name' => 'Gold',
        'price' => '100.00',
        'duration_days' => 30,
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $this->user->memberships()->create([
        'membership_plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $this->actingAs($this->user)
        ->get(route('alumkit.dashboard'))
        ->assertOk()
        ->assertSee('Gold')
        ->assertSee(__('alumkit::membership.status_active'));
});
