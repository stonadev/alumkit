# Release Notes

## [Unreleased](https://github.com/stonadev/alumkit/compare/v0.1.0...1.x)

### Added

- Memberships and manual payments: dashboard-managed membership plans (tiers with a days/months/lifetime term) and a staff-reviewed manual payment ledger (bKash, Nagad, bank transfer). Toggled via `features.memberships` (default `true`); plans are authored only in the dashboard. New permissions `manage membership plans` and `manage memberships` (auto-granted to admin). Read-only facade helpers (`activePlans()`, `membershipFor()`, `hasActiveMembership()`, `formatMoney()`, `paymentMethods()`) and user-model gating helpers (`hasActiveMembership()`, `membershipFeature()`, `hasMembershipFeature()`). Daily `alumkit:memberships:expire` schedule (with derived status on read) flips lapsed terms to `expired`. Memberships are single-currency: amounts render in `alumkit.membership.currency` (default `BDT`) via `formatMoney($amount)`.
- Membership feature gating: while `features.memberships` is enabled, the Member Directory (`members`) and Posts (`posts`) require an active membership whose plan grants the feature, enforced by a `membership:<key>` middleware alias (hard redirect to the membership page) and mirrored in the dashboard sidebar. Admins set these per plan via "Access this plan unlocks" toggles in the plan editor (stored in the plan `features` bag); membership-administration staff bypass the gate. Extensible via `alumkit.membership.gateable_features` and the `$user->canAccessMembershipFeature()` helper. **Behavior change:** with memberships on, approved non-members lose access to Members/Posts until a plan grants them (disable `features.memberships` to keep them open).
- Dashboard-managed payment methods: a `membership_payment_methods` table with a `MembershipPaymentMethod` model and full CRUD behind `manage membership plans` (`alumkit.payment-methods.*`). Methods are typed channels — **bKash**, **Nagad**, or **Bank Transfer**, one method per type — each with rich-text instructions (Editor.js, rendered to HTML on the member form via the shared editor renderer) authored in the dashboard editor; the package ships no default methods. Payments reference a method by its unique `type` (`MembershipPayment::methodLabel()` resolves it), replacing the old static `membership.payment_methods` config array. Staff re-order methods by dragging rows on the dashboard index. The member payment form lists active methods and renders the selected method's instructions.
- Public page routes: `Alumkit::pageRoute($slug)` registers a route that resolves the page, enforces publish state, and renders the schema's registered view (`PageSchema::view()`). Unpublished pages 404 publicly but render as a preview to users holding the `manage pages` permission.
- Redesigned the user review page (`/dashboard/users/{user}`) as a member-profile layout: identity rail with photo, headline role, contact and emergency-contact details; narrative cards for profile details, education, and career; and a review-decision panel with state transitions. ([#DESIGN](DESIGN.md))
- Activation email sent when a pending member is approved or a suspended member is reactivated, with a link back to the dashboard.

### Changed

- Replaced the `user.state` middleware alias with `user.suspended`: suspended users stay signed in and see a suspension banner on the dashboard, but are blocked from all dashboard sub-routes. The session is no longer invalidated on suspension; app-level account routes (Fortify) remain usable while suspended.
- Renamed the user-management page from "Manage User Roles" to "Members" (`manage_user_roles`).
- Membership plans: plan ordering is now drag-and-drop on the Manage Plans dashboard index (new route `alumkit.plans.reorder`); the plan create/edit forms no longer expose a sort-order field, and new plans are appended to the end of the list.

- The registration form's education level field now defaults to the first entry of `alumkit.education.levels` on the first education row; rows added via "Add education" start empty.
### Fixed

- Password fields on auth and profile pages are masked by default and reveal via a working toggle in any host app. The package now ships its own `password` field component (`x-alumkit::password`) instead of relying on the host's TallStackUI component, whose `::type` binding was corrupted by Livewire Blaze and left the password visible.
- PHPStan Windows CI failures: analysis now runs single-process, avoiding a race where parallel Larastan workers collide writing Testbench's `bootstrap/cache/services.php` (`rename(): Access is denied` on Windows).


## [v0.1.0](https://github.com/stonadev/alumkit/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
