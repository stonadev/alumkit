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

### Adding a Custom Feature (e.g. Events)

Alumkit is a toolkit — app-specific features like an events module are plain
Laravel code in your app that plugs into the package's extension points.
End to end:

**1. Publish the config** (once, so your changes survive package updates):

```bash
php artisan alumkit:publish
```

**2. Register the permission** in `config/alumkit.php`, then seed. The
permission is created and automatically assigned to the admin role:

```php
'permission' => [
    'permissions' => ['manage events'],
],
```

```bash
php artisan alumkit:seed
```

**3. Build the feature as normal Laravel app code** — migration, `Event`
model, `EventController`, and views. Nothing package-specific here.

**4. Guard the routes** with the middleware aliases the package registers.
The route names matter: `dashboard_nav` renders `route($item['route'])`, so
the resource must produce `events.index`:

```php
Route::middleware(['web', 'auth', 'user.state', 'complete-profile.check', 'permission:manage events'])
    ->prefix('dashboard')
    ->group(function () {
        Route::resource('events', EventController::class)->except(['show']);
    });
```

**5. Add the sidebar entry** in `config/alumkit.php` — the package layout
renders it automatically, permission-gated per item:

```php
'dashboard_nav' => [
    ['label' => 'Events', 'route' => 'events.index', 'permission' => 'manage events'],
],
```

**6. Render your views inside the package layout** to inherit the sidebar and
auth chrome:

```blade
@extends('alumkit::layouts.dashboard')
@section('content')
    {{-- event CRUD --}}
@endsection
```

### Public Blog API

The package ships no public blog routes — register your own `/posts` and
`/posts/{post}` routes in `routes/web.php` and render them from the facade API:

```php
Route::get('posts', [PostController::class, 'index'])->name('posts.index');
Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');
```

API reference:

- `Alumkit::publishedPosts()` — Eloquent builder of published posts (newest
  first, author eager-loaded); compose with `->get()`, `->paginate()`,
  `->where(...)`.
- `Alumkit::recentPosts(int $limit = 5)` — collection of the `$limit` most
  recent published posts.

In the app's controller, render from the API — e.g.
`view('posts.index', ['posts' => Alumkit::publishedPosts()->paginate(10)])`
for the list, and for the detail `$post = Post::published()->findOrFail($id)`
(404s drafts) — importing `Alumkit\Alumkit\Facades\Alumkit` and
`Alumkit\Alumkit\Models\Post`.

Homepage "recent posts" block:

```blade
<h2>Recent posts</h2>
<ul>
    @foreach (\Alumkit\Alumkit\Facades\Alumkit::recentPosts(5) as $post)
        <li><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></li>
    @endforeach
</ul>
```

(Route name `posts.show` is the app's own; escape/format as needed.)

The dashboard screens are package-owned and not overridable by design.

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
