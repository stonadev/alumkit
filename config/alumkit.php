<?php

declare(strict_types=1);

return [

    'auth' => [

        'user_model' => 'App\\Models\\User',

    ],

    'seeder' => [
        'admin_name' => env('ALUMKIT_ADMIN_NAME', 'Admin'),
        'admin_email' => env('ALUMKIT_ADMIN_EMAIL', 'admin@example.com'),
        'admin_password' => env('ALUMKIT_ADMIN_PASSWORD', 'password'),
    ],

    'features' => [
        // Toggle package features off to hide their routes and dashboard
        // links from the consuming app. Enabled by default.
        'posts' => true,
        'committee' => true,
        'memberships' => true,
    ],

    'permission' => [
        'default_roles' => ['admin', 'moderator', 'member'],
        /*
        |-----------------------------------------------------------------------
        | Permissions
        |-----------------------------------------------------------------------
        | Add your app-specific permissions here. Package permissions (defined
        | in Alumkit::PERMISSIONS) are always seeded and cannot be removed.
        */
        'permissions' => [],
    ],

    'dashboard_nav' => [
        // A link:            ['label' => 'Events', 'route' => 'events.index', 'permission' => 'manage events']
        // permission is optional; omitted -> visible to all authenticated users.
        // A group:           ['label' => 'Settings', 'permission' => 'manage settings', 'children' => [
        //                         ['label' => 'General', 'route' => 'settings.general'],
        //                     ]]
        // group permission is optional and guards the whole group; child permission guards one child.
        // One level of nesting; groups cannot contain groups.

        ['label' => 'Content', 'permission' => 'manage pages', 'children' => [
            ['label' => 'Pages', 'route' => 'alumkit.pages.index'],
            ['label' => 'Globals', 'route' => 'alumkit.globals.index'],
        ]],
    ],

    'education' => [
        'levels' => ['Honors', 'Masters', 'PhD', 'Diploma', 'Certificate'],

        // Suggested levels, institutions and subjects for the education form fields.
        // Consumers seed these lists; users may still type any value.
        'institutions' => [],
        'subjects' => [],
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

    'local_names' => [
        // 'bn' => ['label' => 'বাংলা নাম', 'required' => false],
    ],

    'maintenance' => [
        'enabled' => env('ALUMKIT_MAINTENANCE_ENABLED', false),
    ],

    'membership' => [
        // App-wide currency for all membership money, rendered by
        // Alumkit::formatMoney() (e.g. "BDT 1,500.00").
        'currency' => env('ALUMKIT_MEMBERSHIP_CURRENCY', 'BDT'),

        // Feature keys an admin can gate behind a membership plan. Each is a
        // toggle in the plan editor; when a plan grants a key, members with an
        // active plan reach the matching dashboard area. While the memberships
        // feature is enabled these areas require a granting plan (staff who
        // administer memberships bypass the gate). Apps may extend the list —
        // add a matching `feature_{key}` label to lang/en/membership.php.
        'gateable_features' => ['members', 'posts'],

        // Payment methods (bKash, Nagad, bank transfer) are managed from the
        // dashboard and stored in the database.
        'expiry' => [
            'enabled' => true,
            'at' => '00:30',
        ],

        'proof' => [
            'disk' => 'public',
            'max_kb' => 2048,
            'mimes' => ['jpg', 'jpeg', 'png', 'pdf'],
        ],
    ],

];
