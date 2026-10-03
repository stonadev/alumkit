<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Alumkit\Alumkit\Models\Membership;
use Alumkit\Alumkit\Models\MembershipPlan;
use Illuminate\Database\Seeder;
use Workbench\App\Models\User;

/**
 * Demo-only membership data for the `composer serve` walkthrough. The package
 * itself ships no plan definitions — plans are authored in the dashboard.
 */
class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $monthly = MembershipPlan::updateOrCreate(
            ['name' => 'Alumni Monthly'],
            [
                'name' => 'Alumni Monthly',
                'description' => 'Full access to the alumni network, renewed monthly.',
                'price' => '150.00',
                'duration_days' => 30,
                'is_lifetime' => false,
                'features' => ['directory_access' => 'true', 'event_discount' => '10'],
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        MembershipPlan::updateOrCreate(
            ['name' => 'Alumni Lifetime'],
            [
                'name' => 'Alumni Lifetime',
                'description' => 'One payment, lifetime access for dedicated alumni.',
                'price' => '2500.00',
                'duration_days' => null,
                'is_lifetime' => true,
                'features' => ['directory_access' => 'true', 'event_discount' => '25', 'mentor_badge' => 'true'],
                'sort_order' => 2,
                'is_active' => true,
            ],
        );

        $member = User::where('email', 'approved@example.com')->first();

        if ($member && ! $member->memberships()->exists()) {
            $startsAt = now()->startOfDay();

            Membership::create([
                'user_id' => $member->getKey(),
                'membership_plan_id' => $monthly->getKey(),
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMonth(),
            ]);
        }
    }
}
