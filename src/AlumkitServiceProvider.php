<?php

declare(strict_types=1);

namespace Alumkit\Alumkit;

use Alumkit\Alumkit\Actions\Fortify\CreateNewUser;
use Alumkit\Alumkit\Actions\Fortify\ResetUserPassword;
use Alumkit\Alumkit\Actions\Fortify\UpdateUserPassword;
use Alumkit\Alumkit\Actions\Fortify\UpdateUserProfileInformation;
use Alumkit\Alumkit\Console\Commands\AlumkitCommand;
use Alumkit\Alumkit\Console\Commands\PublishCommand;
use Alumkit\Alumkit\Content\ContentRegistry;
use Alumkit\Alumkit\Http\Livewire\LinkField;
use Alumkit\Alumkit\Http\Livewire\RepeaterField;
use Alumkit\Alumkit\Http\Middleware\CheckUserApproved;
use Alumkit\Alumkit\Http\Middleware\CheckUserSuspended;
use Alumkit\Alumkit\Http\Middleware\CompleteProfileCheck;
use Alumkit\Alumkit\Models\Page;
use Alumkit\Alumkit\Observers\PageObserver;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

class AlumkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/alumkit.php', 'alumkit');
        $this->mergeConfigFrom(__DIR__.'/../config/fortify.php', 'fortify');

        $this->app->singleton(Alumkit::class);
        $this->app->singleton(ContentRegistry::class);
    }

    public function boot(): void
    {
        $this->configureFortifyConfig();

        $this->registerMiddlewareAliases();

        $this->loadRoutesFrom(__DIR__.'/../routes/alumkit.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'alumkit');

        Livewire::component('alumkit.link-field', LinkField::class);
        Livewire::component('alumkit.repeater-field', RepeaterField::class);

        Page::observe(PageObserver::class);

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'alumkit');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->configureFortifyViews();

        $this->configureFortifyActions();

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/alumkit.php' => config_path('alumkit.php'),
        ], ['alumkit-config']);

        $this->commands([
            AlumkitCommand::class,
            PublishCommand::class,
        ]);
    }

    protected function configureFortifyConfig(): void
    {
        config([
            'fortify.home' => '/dashboard',
            'fortify.redirects.login' => '/dashboard',
        ]);

        RedirectIfAuthenticated::redirectUsing(fn () => route('alumkit.dashboard'));
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
        $this->app->make('router')->aliasMiddleware('user.suspended', CheckUserSuspended::class);
        $this->app->make('router')->aliasMiddleware('complete-profile.check', CompleteProfileCheck::class);
        $this->app->make('router')->aliasMiddleware('user.approved', CheckUserApproved::class);
    }
}
