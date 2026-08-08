<?php

declare(strict_types=1);
use Alumkit\Alumkit\Enums\UserState;

return [

    'auth' => [

        'user_model' => 'App\\Models\\User',

    ],

    'seeder' => [
        'admin_name' => env('ALUMKIT_ADMIN_NAME', 'Admin'),
        'admin_email' => env('ALUMKIT_ADMIN_EMAIL', 'admin@example.com'),
        'admin_password' => env('ALUMKIT_ADMIN_PASSWORD', 'password'),
    ],

    'default_state' => UserState::Pending,

    'dashboard_nav' => [
        // A link:            ['label' => 'Events', 'route' => 'events.index', 'permission' => 'manage events']
        // permission is optional; omitted -> visible to all authenticated users.
        // A group:           ['label' => 'Settings', 'permission' => 'manage settings', 'children' => [
        //                         ['label' => 'General', 'route' => 'settings.general'],
        //                     ]]
        // group permission is optional and guards the whole group; child permission guards one child.
        // One level of nesting; groups cannot contain groups.
    ],

    'education' => [
        'levels' => [
            'honors' => 'Honors',
            'masters' => 'Masters',
            'phd' => 'PhD',
            'diploma' => 'Diploma',
            'certificate' => 'Certificate',
        ],
    ],

    'career' => [
        'employment_types' => [
            'full_time' => 'Full-Time',
            'part_time' => 'Part-Time',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
            'internship' => 'Internship',
        ],
    ],

];
