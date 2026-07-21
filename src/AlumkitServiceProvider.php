<?php

declare(strict_types=1);

namespace Alumkit\Alumkit;

use Alumkit\Alumkit\Console\Commands\AlumkitCommand;
use Illuminate\Support\ServiceProvider;

class AlumkitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/alumkit.php', 'alumkit');

        $this->app->singleton(Alumkit::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/alumkit.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'alumkit');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'alumkit');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/alumkit.php' => config_path('alumkit.php'),
        ], ['alumkit', 'alumkit-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/alumkit'),
        ], ['alumkit', 'alumkit-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/alumkit'),
        ], ['alumkit', 'alumkit-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/alumkit'),
        ], ['alumkit', 'alumkit-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['alumkit', 'alumkit-migrations']);

        $this->commands([
            AlumkitCommand::class,
        ]);
    }
}
