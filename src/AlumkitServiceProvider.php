<?php

declare(strict_types=1);

namespace Alumkit\Alumkit;

use Alumkit\Alumkit\Actions\Fortify\CreateNewUser;
use Alumkit\Alumkit\Actions\Fortify\ResetUserPassword;
use Alumkit\Alumkit\Actions\Fortify\UpdateUserPassword;
use Alumkit\Alumkit\Actions\Fortify\UpdateUserProfileInformation;
use Alumkit\Alumkit\Console\Commands\AlumkitCommand;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

class AlumkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/alumkit.php', 'alumkit');
        $this->mergeConfigFrom(__DIR__.'/../config/fortify.php', 'fortify');
        $this->mergeConfigFrom(__DIR__.'/../config/permission.php', 'permission');

        $this->app->singleton(Alumkit::class);
    }

    public function boot(): void
    {
        $this->configureFortifyConfig();

        $this->registerMiddlewareAliases();

        $this->loadRoutesFrom(__DIR__.'/../routes/alumkit.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'alumkit');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'alumkit');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->configureFortifyViews();

        $this->configureFortifyActions();

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/alumkit.php' => config_path('alumkit.php'),
        ], ['alumkit', 'alumkit-config']);

        $this->publishes([
            __DIR__.'/../config/fortify.php' => config_path('fortify.php'),
        ], ['alumkit', 'alumkit-fortify-config']);

        $this->publishes([
            __DIR__.'/../config/permission.php' => config_path('permission.php'),
        ], ['alumkit', 'alumkit-permission-config']);

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

        $this->publishes([
            __DIR__.'/../database/seeders/AlumkitRolesAndPermissionsSeeder.php' => database_path('seeders/AlumkitRolesAndPermissionsSeeder.php'),
        ], ['alumkit', 'alumkit-seeder']);

        $this->publishes([
            __DIR__.'/../database/seeders/AlumkitUserSeeder.php' => database_path('seeders/AlumkitUserSeeder.php'),
        ], ['alumkit', 'alumkit-seeder']);

        $this->commands([
            AlumkitCommand::class,
        ]);
    }

    protected function configureFortifyConfig(): void
    {
        config([
            'fortify.home' => '/dashboard',
            'fortify.redirects.login' => '/dashboard',
        ]);
    }

    protected function configureFortifyViews(): void
    {
        Fortify::loginView(function () {
            return view('alumkit::auth.login');
        });

        Fortify::registerView(function () {
            return view('alumkit::auth.register');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('alumkit::auth.forgot-password');
        });

        Fortify::resetPasswordView(function (Request $request) {
            return view('alumkit::auth.reset-password', ['request' => $request]);
        });

        Fortify::verifyEmailView(function () {
            return view('alumkit::auth.verify-email');
        });

        Fortify::confirmPasswordView(function () {
            return view('alumkit::auth.confirm-password');
        });

        Fortify::twoFactorChallengeView(function () {
            return view('alumkit::auth.two-factor-challenge');
        });
    }

    protected function configureFortifyActions(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
    }

    protected function registerMiddlewareAliases(): void
    {
        $this->app->make('router')->aliasMiddleware('role', RoleMiddleware::class);
        $this->app->make('router')->aliasMiddleware('permission', PermissionMiddleware::class);
        $this->app->make('router')->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
    }
}
