<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Console\Commands;

use Illuminate\Console\Command;

class AlumkitCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'alumkit:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package alumkit.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Alumkit placeholder command executed.');

        return self::SUCCESS;
    }
}
