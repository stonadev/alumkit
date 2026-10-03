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

### The User Model

The package ships `Alumkit\Alumkit\Models\User`, a base model already wired
with email verification (`MustVerifyEmail`), two-factor auth
(`TwoFactorAuthenticatable`), roles (`HasRoles`), notifications
(`Notifiable`), and the profile relations (`HasCareers`, `HasEducations`).
Point your `App\Models\User` at it by extending:

```php
namespace App\Models;

use Alumkit\Alumkit\Models\User as BaseUser;

class User extends BaseUser
{
    // your app-specific columns, casts, factories, and relations
}
```

Registration and the profile-completion flow are gated on email verification.
The base model already implements `Illuminate\Contracts\Auth\MustVerifyEmail`;
without that contract the framework's `verified` middleware silently passes
unverified users, so a fresh registration lands on `/profile/complete`
instead of the verification notice, and no verification email is sent.

### Publishing the Configuration

```bash
php artisan alumkit:publish
```

This copies `config/alumkit.php` to your app's `config/` directory. To overwrite an existing published file, use `php artisan alumkit:publish --force`.

### Feature Toggles

The package includes two built-in features that can be disabled independently. When disabled, a feature's routes return 404 and its links disappear from the dashboard sidebar and quick links. Models, migrations, seeders, and facade helpers remain loaded — re-enabling the flag restores the feature without data loss.

```php
// config/alumkit.php
'features' => [
    'posts' => true,       // set false to hide the posts admin screens
    'committee' => true,   // set false to hide the committee admin screens
],
```

Both are enabled by default. Set a value to `false` in the published config to disable the corresponding feature.

### Education field suggestions

While typing the level, institution and subject fields on education forms, the package suggests values seeded in the published config; users can pick one or type any value:

```php
// config/alumkit.php
'education' => [
    'levels' => ['Honors', 'Masters'],
    'institutions' => ['University of Dhaka', 'MIT', 'Stanford'],
    'subjects' => ['Computer Science', 'Physics'],
],
```

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
- `manage committee`
- `manage pages`
- `view dashboard`
- `view activity log`

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
Route::middleware(['web', 'auth', 'user.suspended', 'complete-profile.check', 'permission:manage events'])
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

### Content Management

The package ships schema-driven content management: **pages** (per-page
content) and **globals** (site-wide singletons). Content editors are defined
by *schemas* you register in a service provider — the package renders the
editor forms from them, and stores the values.

**Pages.** Create pages (`alumkit.pages.*`, guarded by
`permission:manage pages`) with a title, slug, meta description, and publish
state. Each page has a single edit screen
(`alumkit.pages.edit`) whose Content tab fields come from the schema registered
for that page's slug. Deleting a page deletes its content.

**Globals.** Site-wide singletons (`alumkit.globals.*`, guarded by
`permission:manage pages`) — e.g. contact details, footer text. Each
global is one record keyed by a string.

#### Registering Schemas

Register a schema for each page slug and each global key in a service
provider's `boot()`:

```php
use Alumkit\Alumkit\Facades\Alumkit;
use Alumkit\Alumkit\Content\PageSchema;
use Alumkit\Alumkit\Content\SectionSchema;
use Alumkit\Alumkit\Content\GlobalSchema;
use Alumkit\Alumkit\Content\FieldSchema;

Alumkit::page('about', function (PageSchema $page): void {
    $page->view('workbench::about'); // blade that publicly renders the page

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

Alumkit::global('site', function (GlobalSchema $global): void {
    $global->text('contact_email')->label('Contact email');
    $global->textarea('footer_text')->label('Footer text');
});
```

Page schemas are grouped into **sections** (each section is saved as one
`Content` row keyed by its `type`); global schemas are flat (one `Content`
row per global key).

#### Field Types

| Type | Renders |
|---|---|
| `text` | single-line input |
| `textarea` | multi-line input |
| `select` | dropdown; pass `->options(['value' => 'Label'])` |
| `image` | file upload; stored on the public disk and served through the package (no `storage:link`) |
| `checkbox` | boolean toggle |
| `editor` | rich-text editor with image uploads |
| `repeater` | repeating rows of nested fields; pass `->fields([...])` |

All fields accept `->label()`, `->required()`, and `->help()`.

#### Reading Content Back

In your own views or controllers:

```php
// Collection of Content models; key by section type to read fields
$contents = Alumkit::getPageContent('about')->keyBy('type');
$heroBody = $contents->get('hero')?->fields['body'] ?? '';

// Globals: one Content model per key
$email = Alumkit::getGlobalContent('site')->first()?->fields['contact_email'] ?? '';
```

#### Public Page Routes

Register a public route per page in `routes/web.php`; the package resolves the
page, enforces publish state, and renders the schema's registered view:

```php
Route::get('about', Alumkit::pageRoute('about'));
```

The view receives `$page` and `$contents` (the page's `Content` rows keyed by
section type) and reads fields like the editor stores them:

```blade
<h1>{{ $contents->get('hero')?->fields['heading'] ?? 'About' }}</h1>
```

Unpublished pages return 404 to everyone except users holding the
`manage pages` permission, who see a live preview at the same URL.

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

### Public Committee Members API

The package ships no public committee routes — register your own in
`routes/web.php` and query from the facade API:

```php
Route::get('committee', [CommitteeController::class, 'index'])->name('committee.index');
```

API reference:

- `Alumkit::committeeMembers()` — Eloquent builder of all committee members
  sorted by dashboard order (position and user eager-loaded); compose with
  `->get()`, `->paginate()`, `->where(...)`.
- `Alumkit::recentCommitteeMembers(int $limit = 0)` — collection of the
  `$limit` most recent committee members; limit `0` (the default) returns
  all.

In the app's controller, render from the API — e.g.
`view('committee.index', ['members' => Alumkit::committeeMembers()->get()])`.

Homepage "committee" block:

```blade
<h2>Committee</h2>
<ul>
    @foreach (\Alumkit\Alumkit\Facades\Alumkit::recentCommitteeMembers(5) as $member)
        <li>
            {{ $member->user->name }}
            — {{ $member->position->title }}
        </li>
    @endforeach
</ul>
```

The dashboard screens are package-owned and not overridable by design.

### Memberships & Manual Payments

Alumkit ships a dashboard-managed membership system: tiered plans with terms
and manual/offline payments reviewed by staff.
There is no payment gateway and no invoice entity — members declare a payment
(bKash, Nagad, or bank transfer) and staff approve or reject it.

The feature is on by default and toggled like the others:

```php
// config/alumkit.php
'features' => [
    'memberships' => true, // set false to hide all membership routes and links
],
```

When disabled, membership routes 404 and every dashboard entry disappears;
models, migrations, and facade helpers stay loaded, so re-enabling restores
the feature without data loss.

**Plans are authored only in the dashboard** — there is no config array, seeder,
or artisan command for plans. Staff holding `manage membership plans` create and
edit tiers under **Membership → Manage Plans** (`alumkit.plans.*`). Each plan
has a price, a features map, and **exactly one term**:

- a base duration stored in days — `duration_days` (e.g. 30). The dashboard
  also accepts a month term and stores it as days (12 months → 360), **or**
- a lifetime flag (`is_lifetime`, with `duration_days` null).

**Single currency.** Memberships are single-currency: every membership amount
is stored in the app-wide `alumkit.membership.currency` (env
`ALUMKIT_MEMBERSHIP_CURRENCY`, default `BDT`) and rendered by
`Alumkit::formatMoney($amount)`.

**Payment methods are dashboard-managed.** Staff holding `manage membership
plans` maintain the ways members can pay under **Membership → Payment
Methods** (`alumkit.payment-methods.*`). Each method is a typed channel —
**bKash**, **Nagad**, or **Bank Transfer** (one method per type; payments
reference the method by that type) plus rich-text instructions carrying the
pay-to details (account numbers, bank details, QR codes, etc.). Staff create
methods from the dashboard — the package ships no default methods — and
re-order them by dragging rows on the index. Inactive methods are hidden from
the member form. When a member pays, the form lists the active methods and
shows the selected method's instructions.

**Payment flow.** A member opens **Membership → Pay for this plan**, submits a
payment for the plan's price (the request re-validates the amount, so a payment
can never be over- or under-charged), and staff review it
under **Payment Queue** (`alumkit.payments.*`). Approving activates or extends
the membership; rejecting requires a reason and emails the member. Approving
extends from the current `ends_at` (remaining time is preserved); a lifetime
membership is never shortened or downgraded. Members must have an Active
account (`user.approved`), matching the member directory.

**Expiry.** The package schedules `alumkit:memberships:expire` daily
(`membership.expiry.at`, default `00:30`) to flip lapsed, non-lifetime rows to
`expired`. Status is also derived on read, so a missed run never shows a lapsed
membership as active.

**Read-only facade API** for app-side pricing pages and gating (plan authoring
stays in the dashboard):

- `Alumkit::activePlans()` — collection of active plans (ordered by `sort_order`).
- `Alumkit::membershipFor(User $user): ?Membership` — the user's current membership.
- `Alumkit::hasActiveMembership(User $user): bool` — gate features on membership.
- `Alumkit::formatMoney(float|string $amount): string` — renders in the app-wide membership currency, e.g. `"BDT 1,500.00"`.
- `Alumkit::paymentMethods(): array<string,string>` — the active,
  dashboard-managed methods (`slug => name`).

On the user model, the package adds membership helpers for gating:

```php
if ($user->hasActiveMembership() && $user->hasMembershipFeature('event_discount')) {
    // ...
}

$feature = $user->membershipFeature('event_discount'); // raw string value or null
```

A features bag is a flat `string => string` map; `hasMembershipFeature()` treats
`1`, `true`, `yes`, and `on` as enabled.

**Gated features.** While `features.memberships` is enabled, two dashboard areas
are gated behind membership: the **Member Directory** (`members`) and **Posts**
(`posts`). An approved user needs an active membership whose plan grants the
feature to reach them; otherwise they are redirected to their membership page.
Staff who administer memberships (`manage members`, `manage memberships`, or
`manage membership plans`) always bypass the gate.

Admins set these as toggles in the plan editor ("Access this plan unlocks"), which are
stored in the same `features` bag (`members => "1"`, `posts => "1"`). The
textarea beside them is for app-specific value features like `event_discount`.

> **Behavior change:** with memberships enabled, approved non-members lose
> access to the Member Directory and Posts until a plan grants them (or they are
> staff). Disable `features.memberships` to keep them open to all approved users.

Apps can gate their own dashboard areas the same way: add a key to
`alumkit.membership.gateable_features`, add a matching `feature_{key}` label to
`lang/en/membership.php`, and protect the route with the `membership:<key>`
middleware alias (e.g. `->middleware('membership:members')`). The user-model
helper `$user->canAccessMembershipFeature('members')` exposes the same check for
views and controllers.

**Events and notifications.** Activation and rejection dispatch events
(`PaymentSubmitted`, `PaymentApproved`, `PaymentRejected`,
`MembershipActivated`, `MembershipExpired`) and queue
a mail notification to the member. Apps can extend via these events; there are
no renewal reminders.

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

### Field components

Alumkit ships reusable Blade field components so forms in your own views stay
consistent with the package.

#### Link field

A control that opens a modal where you set a label and a URL. While typing the
URL, it suggests the app's named routes that have no required parameters
(matched by route name and URI); you can still type any custom URL.

```blade
<form method="POST" action="{{ route('events.store') }}">
    @csrf

    <x-alumkit::link-field name="website" label="Website" value="https://example.com" />

    <x-button type="submit" :text="__('Save')" />
</form>
```

Contract:

- `name` (required) — the form key. The component posts two hidden inputs,
  `{name}[label]` and `{name}[url]`, always present (empty strings when
  unset). Read them together:
  ```php
  $link = $request->input('website'); // ['label' => ..., 'url' => ...]
  ```
- `label` (optional) — the field title shown above the control (the label of
  the link itself is authored inside the modal).
- `value` (optional) — the initial URL. Pass `old('website.url')` to keep
  the value across a validation round-trip.

The URL input suggests up to 8 named routes without required parameters while
you type; picking one fills the link label from the route name (editable in
the modal). The component needs Livewire, which is already installed as a
dependency of TallStackUi.

#### Select

```blade
<x-alumkit::select name="level" :label="__('education.level')"
    :options="['honors' => 'Honors', 'phd' => 'PhD']" value="phd" required />
```

Contract: `name` (required), `label` (optional), `options` (associative
array of value → label, default `[]`), `value` (optional, the selected
value), `required` (optional). Renders label, select, and validation error
for `name`.

#### Textarea

```blade
<x-alumkit::textarea name="description" :label="__('career.description')"
    value="Old text" rows="6" required />
```

Contract: `name` (required), `label` (optional), `value` (optional, initial
text), `rows` (optional, default `4`), `required` (optional). Renders label,
textarea, and validation error for `name`.

#### Checkbox

```blade
<x-alumkit::checkbox name="published" :label="__('post.publish')" :checked="$post->isPublished()" />
```

Contract: `name` (required), `label` (optional), `value` (optional, checked
value, default `1`), `uncheckedValue` (optional, default `0`), `checked`
(optional, default `false`). Renders a hidden `uncheckedValue` input plus the
checkbox, so the field always submits. Extra attributes (for example
`x-model`) are forwarded to the checkbox input.

#### Password

```blade
<x-alumkit::password name="password" :label="__('auth.password')" required
    autocomplete="current-password" />
```

Contract: `name` (required), `label` (optional), `required` (optional),
`autocomplete` (optional, default `off`). Renders a password input with an
eye toggle that reveals the value; the input stays masked by default and the
toggle uses Alpine's `x-data`/`:type` bindings, so it works in any host app
without relying on framework attribute normalization. Extra attributes
(for example `autofocus`) are forwarded to the input, and a validation
error for `name` renders below the field.

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
