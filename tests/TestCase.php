<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Tests;

use Alumkit\Alumkit\AlumkitServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Route;
use Laravel\Fortify\FortifyServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use TallStackUi\Facades\TallStackUi;
use TallStackUi\TallStackUiServiceProvider;
use Workbench\App\Models\User;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Testbench's app builder never calls withEvents(), so the framework's
        // email-verification listener is missing. Register it like a real app.
        $this->app['events']->listen(Registered::class, SendEmailVerificationNotification::class);

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'Workbench\\Database\\Factories\\'.class_basename($modelName).'Factory';
        });

        AliasLoader::getInstance()->alias('TallStackUi', TallStackUi::class);

        $this->app['request']->setRouteResolver(function () {
            return new Route('GET', '/', static fn () => '');
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            TallStackUiServiceProvider::class,
            FortifyServiceProvider::class,
            PermissionServiceProvider::class,
            ActivitylogServiceProvider::class,
            AlumkitServiceProvider::class,
        ];
    }

    protected function resolveApplicationConfiguration($app): void
    {
        // `alumkit:publish` copies config to config_path(), which by default is
        // testbench's shared skeleton config dir — the same dir every parallel
        // Pest worker scans in LoadConfiguration during app bootstrap. Writing
        // and deleting alumkit.php there races with other workers' bootstraps
        // ("ValueError: Path cannot be empty" / "Failed opening required").
        // Point config_path() at a per-process scratch dir so publish tests
        // never touch the shared skeleton. Runs before providers boot, so the
        // publish target registered by AlumkitServiceProvider follows along.
        $app->useConfigPath(sys_get_temp_dir().'/alumkit-test-config-'.getmypid());

        parent::resolveApplicationConfiguration($app);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode('12345678901234567890123456789012'));
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('alumkit.auth.user_model', User::class);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cache.store', 'array');
        $app['config']->set('cache.default', 'array');

        $app['config']->set('fortify.home', '/dashboard');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
