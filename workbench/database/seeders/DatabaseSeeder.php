<?php

namespace Workbench\Database\Seeders;

use Alumkit\Alumkit\Database\Seeders\AlumkitRolesAndPermissionsSeeder;
use Alumkit\Alumkit\Database\Seeders\AlumkitUserSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AlumkitRolesAndPermissionsSeeder::class,
            AlumkitUserSeeder::class,
        ]);

        UserFactory::new()->create([
            'name' => 'Verified User',
            'email' => 'verified@example.com',
        ]);

        UserFactory::new()->unverified()->create([
            'name' => 'Unverified User',
            'email' => 'unverified@example.com',
        ]);
    }
}
