---
name: alumkit-development
description: >
  Configure and apply the Alumkit package in Laravel applications.
license: MIT
metadata:
  author: Shuvo Paul
---

# Alumkit

Use this skill when a Laravel application needs to integrate the Alumkit package.

## Primary Goal

- apply the `stonadev/alumkit` package's public API in the smallest correct way

## Workflow

### 1. Inspect the Laravel app context

- confirm the app is a Laravel project
- inspect the target code paths where the package should be applied

### 2. Apply the package's public API

#### Install and publish

- require `stonadev/alumkit` via Composer; run `php artisan migrate` to create the package tables
- run `php artisan alumkit:publish` to copy `config/alumkit.php` into the app; use `--force` to overwrite an existing published file

#### Permissions

- built-in permissions (`manage roles`, `manage permissions`, `manage members`, `manage educations`, `view dashboard`) are always seeded and cannot be removed
- to add app permissions, publish the config and extend `permission.permissions`; run `php artisan alumkit:seed` to create them and assign them to the admin role
- guard app routes with the registered middleware aliases: `permission:manage events`, `role:admin`, `role_or_permission:...`, plus `user.state` and `complete-profile.check`

#### Dashboard sidebar

- the package dashboard layout renders `config('alumkit.dashboard_nav')`; each item is `['label' => ..., 'route' => ..., 'permission' => ...]` (permission optional) and groups nest one level via `children`
- routes named in `dashboard_nav` must exist; the permission entry hides the link from users lacking it

#### Public blog

- the package registers no public blog routes — register the app's own `/posts` and `/posts/{post}` routes and render them from the facade API: `Alumkit::publishedPosts()` (builder, newest first, author loaded) and `Alumkit::recentPosts($limit)` for a homepage block
- for a detail page, `Post::published()->findOrFail($id)` 404s drafts
- dashboard screens are package-owned and not overridable

#### Custom features (e.g. Events)

- a feature is plain Laravel app code (migration, model, controller, views) plugged into package extension points:
  1. add the permission to `permission.permissions` and run `php artisan alumkit:seed`
  2. define routes under the package middleware stack (`web`, `auth`, `user.state`, `complete-profile.check`, `permission:manage events`); `Route::resource` names like `events.index` are what `dashboard_nav` resolves
  3. add the nav entry to `dashboard_nav`
  4. extend `alumkit::layouts.dashboard` in views to inherit the sidebar and auth chrome

#### Field components — Link field

- render `<x-alumkit::link-field name="website" label="Website" value="https://example.com" />` inside consumer forms to add a link field (modal with label + URL inputs)
- the component posts `{name}[label]` and `{name}[url]` hidden inputs (always present, empty string when unset); read both via `$request->input('website')`
- `name` is required (plain key, no brackets); `label` is the field title; `value` is the initial URL (pass `old('website.url')` on validation round-trips)
- the URL input suggests up to 8 of the app's named routes without required parameters while typing; custom URLs are always allowed; picking a route auto-fills the link label from the route name (editable in the modal)

## Rules, References, and Templates

Read before executing:

- `README.md` in the package root for the full walkthrough
- published `config/alumkit.php` for `permission.permissions` and `dashboard_nav` shape
- `routes/alumkit.php` in the package for the resource-route and middleware pattern

## Examples

- an app adds an events module: register `manage events` in `permission.permissions`, seed, create `EventController` behind `permission:manage events`, add `['label' => 'Events', 'route' => 'events.index', 'permission' => 'manage events']` to `dashboard_nav`, and render CRUD views inside `alumkit::layouts.dashboard`

## Anti-patterns

- do not document package internals here; keep the skill focused on adoption in Laravel apps
- do not claim features the package does not provide (e.g. app-specific modules like events are app code, not package features)
