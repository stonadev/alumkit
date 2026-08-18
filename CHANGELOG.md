# Release Notes

## [Unreleased](https://github.com/stonadev/alumkit/compare/v0.1.0...1.x)

### Fixed

- Password fields on auth and profile pages are masked by default and reveal via a working toggle in any host app. The package now ships its own `password` field component (`x-alumkit::password`) instead of relying on the host's TallStackUI component, whose `::type` binding was corrupted by Livewire Blaze and left the password visible.
- PHPStan Windows CI failures: analysis now runs single-process, avoiding a race where parallel Larastan workers collide writing Testbench's `bootstrap/cache/services.php` (`rename(): Access is denied` on Windows).


## [v0.1.0](https://github.com/stonadev/alumkit/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
