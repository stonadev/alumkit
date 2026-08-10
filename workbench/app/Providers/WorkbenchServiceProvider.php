<?php

namespace Workbench\App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Models\User;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        config(['cache.default' => 'array']);
        config(['alumkit.auth.user_model' => User::class]);

        // Testbench's skeleton app never calls withEvents(), so wire the
        // framework's email-verification listener like a real Laravel app.
        Event::listen(Registered::class, SendEmailVerificationNotification::class);
    }
}
