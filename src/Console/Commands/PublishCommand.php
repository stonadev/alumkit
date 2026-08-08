<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Console\Commands;

use Alumkit\Alumkit\AlumkitServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class PublishCommand extends Command
{
    /**
     * The filesystem instance.
     */
    protected Filesystem $files;

    /**
     * The command signature.
     */
    protected $signature = 'alumkit:publish {--force : Overwrite existing published files}';

    /**
     * The command description.
     */
    protected $description = 'Publish all Alumkit resources.';

    /**
     * Create a new command instance.
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $paths = ServiceProvider::pathsToPublish(AlumkitServiceProvider::class);

        foreach ($paths as $from => $to) {
            if ($this->files->isFile($from)) {
                $this->publishFile($from, $to);
            } elseif ($this->files->isDirectory($from)) {
                $this->publishDirectory($from, $to);
            } else {
                $this->components->error("Can't locate path: <{$from}>");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Publish the file, skipping existing files unless --force is given.
     */
    protected function publishFile(string $from, string $to): void
    {
        if (! $this->option('force') && $this->files->exists($to)) {
            $this->components->twoColumnDetail(
                sprintf('File [%s] already exists', $to),
                '<fg=yellow;options=bold>SKIPPED</>',
            );

            return;
        }

        $this->files->ensureDirectoryExists(dirname($to));

        $this->files->copy($from, $to);

        $this->components->task(sprintf('Copying file [%s] to [%s]', $from, $to));
    }

    /**
     * Publish every file in the directory, skipping existing files unless --force is given.
     */
    protected function publishDirectory(string $from, string $to): void
    {
        foreach ($this->files->allFiles($from) as $file) {
            $this->publishFile($file->getPathname(), $to.'/'.$file->getRelativePathname());
        }
    }
}
