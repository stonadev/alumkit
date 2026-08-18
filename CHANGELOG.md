# Release Notes

## [Unreleased](https://github.com/stonadev/alumkit/compare/v0.1.0...1.x)

### Added

- Redesigned the user review page (`/dashboard/users/{user}`) as a member-profile layout: identity rail with photo, headline role, contact and emergency-contact details; narrative cards for profile details, education, and career; and a review-decision panel with state transitions. ([#DESIGN](DESIGN.md))

### Changed

- Replaced the `user.state` middleware alias with `user.suspended`: suspended users stay signed in and see a suspension banner on the dashboard, but are blocked from all dashboard sub-routes. The session is no longer invalidated on suspension; app-level account routes (Fortify) remain usable while suspended.
- Renamed the user-management page from "Manage User Roles" to "Members" (`manage_user_roles`).
### Fixed

- Password fields on auth and profile pages are masked by default and reveal via a working toggle in any host app. The package now ships its own `password` field component (`x-alumkit::password`) instead of relying on the host's TallStackUI component, whose `::type` binding was corrupted by Livewire Blaze and left the password visible.
- PHPStan Windows CI failures: analysis now runs single-process, avoiding a race where parallel Larastan workers collide writing Testbench's `bootstrap/cache/services.php` (`rename(): Access is denied` on Windows).


## [v0.1.0](https://github.com/stonadev/alumkit/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
