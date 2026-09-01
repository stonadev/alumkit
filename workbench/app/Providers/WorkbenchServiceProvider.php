<?php

namespace Workbench\App\Providers;

use Alumkit\Alumkit\Content\FieldSchema;
use Alumkit\Alumkit\Content\GlobalSchema;
use Alumkit\Alumkit\Content\PageSchema;
use Alumkit\Alumkit\Content\SectionSchema;
use Alumkit\Alumkit\Facades\Alumkit;
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
        // Register CMS schemas so the pages/globals editor screens resolve
        // them at request time. ContentSeeder re-registers the same schemas
        // (idempotently) so standalone seeder runs stay self-contained.
        Alumkit::page('about', function (PageSchema $page): void {
            $page->section('hero', function (SectionSchema $section): void {
                $section->text('heading')->label('Heading')->required();
                $section->editor('body')->label('Body');
            });

            $page->section('team', function (SectionSchema $section): void {
                $section->repeater('members')->fields([
                    (new FieldSchema('name', 'text'))->label('Name'),
                    (new FieldSchema('role', 'text'))->label('Role'),
                ]);
            });
        });

        Alumkit::global('homepage', function (GlobalSchema $global): void {
            $global->text('hero_heading')->label('Hero heading');
            $global->textarea('welcome_text')->label('Welcome text');
        });

        Alumkit::global('contact', function (GlobalSchema $global): void {
            $global->text('contact_email')->label('Contact email');
            $global->text('phone')->label('Phone');
            $global->textarea('address')->label('Address');
        });

        config(['cache.default' => 'array']);
        config(['alumkit.auth.user_model' => User::class]);
        config(['alumkit.education.institutions' => ['University of Dhaka', 'MIT', 'Stanford'], 'alumkit.education.subjects' => ['Computer Science', 'Physics']]);

        // Testbench's skeleton app never calls withEvents(), so wire the
        // framework's email-verification listener like a real Laravel app.
        Event::listen(Registered::class, SendEmailVerificationNotification::class);
    }
}
