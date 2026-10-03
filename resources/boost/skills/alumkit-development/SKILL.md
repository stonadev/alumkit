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

- built-in permissions (`manage roles`, `manage permissions`, `manage members`, `manage educations`, `manage committee`, `manage pages`, `manage membership plans`, `manage memberships`, `view dashboard`, `view activity log`) are always seeded and cannot be removed
- to add app permissions, publish the config and extend `permission.permissions`; run `php artisan alumkit:seed` to create them and assign them to the admin role
- guard app routes with the registered middleware aliases: `permission:manage events`, `role:admin`, `role_or_permission:...`, plus `user.suspended` and `complete-profile.check`

#### Dashboard sidebar

- the package dashboard layout renders `config('alumkit.dashboard_nav')`; each item is `['label' => ..., 'route' => ..., 'permission' => ...]` (permission optional) and groups nest one level via `children`
- routes named in `dashboard_nav` must exist; the permission entry hides the link from users lacking it

#### Public pages

- register a public route per page with `Route::get('about', Alumkit::pageRoute('about'))`; the package resolves the page, enforces publish state, and renders the schema's view
- set the rendering template on the schema: `Alumkit::page('about', fn (PageSchema $page) => $page->view('workbench::about')->section(...))`
- the route closure passes `$page` and `$contents` (page `Content` rows keyed by section type) to the view; read fields like `$contents->get('hero')?->fields['heading'] ?? ''`
- unpublished pages 404 publicly but render to users holding the `manage pages` permission (preview); a page whose schema has no view also 404s

#### Custom features (e.g. Events)

- a feature is plain Laravel app code (migration, model, controller, views) plugged into package extension points:
  1. add the permission to `permission.permissions` and run `php artisan alumkit:seed`
  2. define routes under the package middleware stack (`web`, `auth`, `user.suspended`, `complete-profile.check`, `permission:manage events`); `Route::resource` names like `events.index` are what `dashboard_nav` resolves
  3. add the nav entry to `dashboard_nav`
  4. extend `alumkit::layouts.dashboard` in views to inherit the sidebar and auth chrome

#### Memberships

- the membership feature is on by default; set `features.memberships` to `false` in the published config to hide all membership routes and dashboard links (models/migrations/facade helpers stay loaded)
- plans are authored only in the dashboard by staff holding `manage membership plans` (`alumkit.plans.*`); there is no config array, seeder, or command for plans — do not try to define tiers in app code
- each plan has exactly one term: a base `duration_days` count (the dashboard also accepts a month term and stores it as days, 12 months → 360) or `is_lifetime`; plans always charge their configured price — there are no discount windows or zero-cost self-serve activations; members pay through the manual payment flow staff then review
- staff review member-declared payments under `alumkit.payments.*` behind `manage memberships`; approving activates/extends, rejecting requires a reason; manual/offline methods only (no gateway)
- payment methods (bKash, Nagad, bank transfer) are dashboard-managed under `alumkit.payment-methods.*` behind `manage membership plans`; staff create methods from the dashboard — there is no seeder, no config array for methods. Each method is a typed channel (one per type; payments reference it by `type`) plus rich-text instructions (account numbers, bank details, etc.) authored in the editor; ordering is drag-and-drop on the index (`payment-methods/reorder`). The member payment form lists active methods and shows the selected method's instructions; `Alumkit::paymentMethods()` returns `type => label`
- gate app features on membership via the user model: `$user->hasActiveMembership()`, `$user->hasMembershipFeature('event_discount')`, `$user->membershipFeature('key')`
- while memberships are enabled, the Member Directory (`members`) and Posts (`posts`) are gated behind membership: an approved user needs an active plan that grants the feature (staff administering memberships bypass). Admins toggle these per plan in the plan editor; gate your own dashboard areas via `alumkit.membership.gateable_features` + the `membership:<key>` middleware alias and `$user->canAccessMembershipFeature('key')`
- build app-side pricing/gating from the read-only facade: `Alumkit::activePlans()`, `Alumkit::membershipFor($user)`, `Alumkit::hasActiveMembership($user)`, `Alumkit::formatMoney($amount)`, `Alumkit::paymentMethods()`

#### Field components

- render `<x-alumkit::link-field name="website" label="Website" value="https://example.com" />` inside consumer forms to add a link field (modal with label + URL inputs)
- the component posts `{name}[label]` and `{name}[url]` hidden inputs (always present, empty string when unset); read both via `$request->input('website')`
- `name` is required (plain key, no brackets); `label` is the field title; `value` is the initial URL (pass `old('website.url')` on validation round-trips)
- the URL input suggests up to 8 of the app's named routes without required parameters while typing; custom URLs are always allowed; picking a route auto-fills the link label from the route name (editable in the modal)
- render `<x-alumkit::select name="level" :label="__('education.level')" :options="['honors' => 'Honors']" value="phd" required />` for a select; `options` is an associative value → label array, `value` preselects, `required` adds the HTML attribute; label and validation error for `name` render automatically
- render `<x-alumkit::textarea name="description" label="Description" value="Old text" rows="6" />` for a textarea; `rows` defaults to 4
- render `<x-alumkit::checkbox name="published" label="Publish" :checked="$post->isPublished()" />` for a checkbox; it renders a hidden `value="0"` input alongside so the field always submits; `value`/`uncheckedValue` default to `1`/`0`; extra attributes like `x-model` land on the checkbox input
- render `<x-alumkit::password name="password" label="Password" required autocomplete="current-password" />` for a password field; the input is masked by default with an eye toggle (Alpine `x-data`/`:type`), `autocomplete` defaults to `off`, and extra attributes like `autofocus` land on the input; label and validation error for `name` render automatically

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
