<div align="center">
    <h1>Alumkit</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/stonadev/alumkit"><img src="https://img.shields.io/packagist/v/stonadev/alumkit.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/stonadev/alumkit"><img src="https://img.shields.io/packagist/php-v/stonadev/alumkit.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/stonadev/alumkit"><img src="https://badge.laravel.cloud/badge/stonadev/alumkit?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/stonadev/alumkit/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/stonadev/alumkit/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/stonadev/alumkit"><img src="https://img.shields.io/packagist/dt/stonadev/alumkit.svg?style=flat-square" alt="Total Downloads"></a>
</p>

A Laravel toolkit for alumni management applications.

## Installation

You can install the package via Composer:

```bash
composer require stonadev/alumkit
```

The package registers its migrations — run `php artisan migrate` to create the tables.

### Publishing the Configuration

```bash
php artisan alumkit:publish
```

This copies `config/alumkit.php` to your app's `config/` directory. To overwrite an existing published file, use `php artisan alumkit:publish --force`.

## Usage

### Permissions

The package ships with a role-based permission system powered by [Spatie Laravel Permission](https://github.com/spatie/laravel-permission).

#### Built-in Roles and Permissions

Three roles are seeded by default:

| Role | Permissions |
|---|---|
| **admin** | All permissions |
| **moderator** | `manage members`, `view dashboard` |
| **member** | _(none)_ |

The following permissions are always seeded and cannot be removed:

- `manage roles`
- `manage permissions`
- `manage members`
- `manage educations`
- `view dashboard`

#### Extending with Custom Permissions

To add permissions for your app's features, publish the config and add entries to `permission.permissions`:

```bash
php artisan alumkit:publish
```

```php
// config/alumkit.php
'permission' => [
    'permissions' => [
        'manage events',
        'manage announcements',
    ],
],
```

Then seed the roles and permissions:

```bash
php artisan alumkit:seed
```

Custom permissions are created alongside the built-in ones and automatically assigned to the admin role.

Guard your own routes using the middleware aliases registered by the package:

```php
Route::middleware('permission:manage events')->group(function () {
    Route::resource('events', EventController::class);
});
```

Add links for your features to the package dashboard sidebar via the published `config/alumkit.php`:

```php
// config/alumkit.php
'dashboard_nav' => [
    ['label' => 'Events', 'route' => 'events.index', 'permission' => 'manage events'],
    [
        'label' => 'Settings',
        'children' => [
            ['label' => 'General', 'route' => 'settings.general'],
        ],
    ],
],
```

Links and group children render in the package dashboard sidebar; entries with a `permission` are hidden from users lacking that permission.

#### Seeding the Admin User

The admin user created by `AlumkitUserSeeder` is configured via environment variables:

| Variable | Default |
|---|---|
| `ALUMKIT_ADMIN_NAME` | `Admin` |
| `ALUMKIT_ADMIN_EMAIL` | `admin@example.com` |
| `ALUMKIT_ADMIN_PASSWORD` | `password` |

Set these in your app's `.env` before running `php artisan alumkit:seed`. If you cache config
(`php artisan config:cache`), re-cache after changing them. Values are read
per-key, so setting only `ALUMKIT_ADMIN_EMAIL` keeps the name/password defaults.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Alumkit! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Shuvo Paul](https://github.com/stonadev)
- [All Contributors](../../contributors)

## License

Alumkit is open-sourced software licensed under the [MIT license](LICENSE.md).
