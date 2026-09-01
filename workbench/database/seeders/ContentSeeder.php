<?php

namespace Workbench\Database\Seeders;

use Alumkit\Alumkit\Content\FieldSchema;
use Alumkit\Alumkit\Content\GlobalSchema;
use Alumkit\Alumkit\Content\PageSchema;
use Alumkit\Alumkit\Content\SectionSchema;
use Alumkit\Alumkit\Facades\Alumkit;
use Alumkit\Alumkit\Models\Content;
use Alumkit\Alumkit\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the CMS content for the workbench app.
     */
    public function run(): void
    {
        // Re-registering the schemas keeps the seeder self-contained for
        // standalone runs; the WorkbenchServiceProvider registration is the
        // canonical one for the running app. Both are idempotent.
        Alumkit::page('about', function (PageSchema $page): void {
            $page->view('workbench::about');

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

        Page::updateOrCreate(['slug' => 'about'], [
            'title' => 'About',
            'meta_title' => 'About the Alumni Network',
            'meta_description' => 'Learn about the alumni network and its members.',
            'is_published' => true,
        ]);

        Content::updateOrCreate(['owner' => 'page:about', 'type' => 'hero'], [
            'fields' => [
                'heading' => 'About Alumkit',
                'body' => 'Alumkit is a Laravel toolkit for alumni management applications.',
            ],
        ]);

        Content::updateOrCreate(['owner' => 'page:about', 'type' => 'team'], [
            'fields' => [
                'members' => [
                    ['name' => 'Ayesha Rahman', 'role' => 'Founder & Director'],
                    ['name' => 'Tanvir Rahman', 'role' => 'Administrator'],
                ],
            ],
        ]);

        Content::updateOrCreate(['owner' => 'global:homepage', 'type' => 'global'], [
            'fields' => [
                'hero_heading' => 'Welcome to the Alumni Network',
                'welcome_text' => 'Stay connected with classmates and the university community.',
            ],
        ]);

        Content::updateOrCreate(['owner' => 'global:contact', 'type' => 'global'], [
            'fields' => [
                'contact_email' => 'alumni@example.com',
                'phone' => '+880 1711 000 000',
                'address' => '12 Lakeview Road, Dhaka 1212',
            ],
        ]);
    }
}
