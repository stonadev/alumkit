<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

/**
 * Seeds enough users to exercise pagination on /dashboard/users.
 *
 * Called by `composer build` — never by DatabaseSeeder, so tests stay lean.
 */
class BulkUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'bulk-pagination-0@example.com')->exists()) {
            return;
        }

        $bulkStates = array_merge(
            array_fill(0, 18, 'approved'),
            array_fill(18, 4, 'pending'),
            array_fill(22, 2, 'unverified'),
        );

        foreach ($bulkStates as $i => $state) {
            $method = $state === 'approved' ? 'approved' : ($state === 'pending' ? 'pending' : 'unverified');

            UserFactory::new()->$method()->create([
                'email' => "bulk-pagination-{$i}@example.com",
            ]);
        }
    }
}
