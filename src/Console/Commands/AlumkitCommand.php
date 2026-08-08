<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Console\Commands;

use Alumkit\Alumkit\Database\Seeders\AlumkitRolesAndPermissionsSeeder;
use Alumkit\Alumkit\Database\Seeders\AlumkitUserSeeder;
use Illuminate\Console\Command;

class AlumkitCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'alumkit:seed';

    /**
     * The command description.
     */
    protected $description = 'Seed Alumkit roles, permissions, and the admin user.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        (new AlumkitRolesAndPermissionsSeeder)->run();
        (new AlumkitUserSeeder)->run();

        $this->info('Alumkit roles, permissions, and admin user seeded.');

        return self::SUCCESS;
    }
}
