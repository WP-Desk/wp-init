# wp-init changelog

## [1.0.0] - Unreleased
### Changed
- Replaced extension-style bootstrap behavior with explicit modules and boot gates.
- Tightened configuration around canonical `hooks`, `services`, `modules`, `activation`, and `deactivation` keys.
- Activation and deactivation handlers are now explicit first-class lifecycle hooks.
- Invalid bindings and invalid enabled module config now fail loudly instead of being ignored.
- Legacy builder support is now an explicit module configured under `modules`.
- Requirements, tracker, and licensing integrations are module-driven instead of auto-enabled by package detection.
- Removed the `wpinit` CLI helper from the public package surface.

## [0.10.6] - 2025-01-10
### Fixed
- Fixed path for loading plugin translations.
### Changed
- Improved _development_ environment detection. Now, in addition to previous methods, adding `dev` suffix to plugin version enabled development environment (no container compilation).

## [0.10.5] - 2025-01-09
### Fixed
- Fixed condition checking if current environment is development, causing container to always use live version.

## [0.10.4] - 2024-11-27
### Fixed
- When container cannot be compiled to disk, it will be used without cache.
### Changed
- Serious changes in `StoppableBinder` logic. It can only stop hookable classes from now, leaving the execution of callable shortcut bindings.
- Improvements in dev environment detection – container is not compiled, when WordPress environment type is different from `production`.
- Reverted: Move i18n filter to `init` hook for WordPress 6.7 compatibility.

## [0.10.3] - 2024-11-13
### Changed
- Move i18n filter to `init` hook for WordPress 6.7 compatibility.

## [0.10.2] - 2024-10-08
### Fixed
- Typo in configuration parameter name.

## [0.10.1] - 2024-10-08
### Changed
- `wpinit` CLI command automatically seeks for potential plugin file, instead of relying on passing it as argument.

## [0.10.0] - 2024-10-07
### Added
- Dependency injection container compilation on first request.
- Integration with `wpdesk/wp-wpdesk-license`, enabled when library is available.
- Hook definitions can now include optional `priority` parameter, which defines the order of hooking into WordPress. This may be useful, when result of one action would prevent other actions from execution. Example:
```php
return [
  ClassRequiringWooCommerce::class,
  'plugins_loaded' => [
    OtherClassRequiringWooCommerce::class
  ],
  [
    // This will be hooked first, and may possibly terminate the rest of the hooks.
    'priority' => -100,
    'handler' => CheckIfWooCommerceAvailable::class
  ],
];
```
### Changed
- Enabling legacy mode requires verbosely activating that in configuration with `'legacy' => true`.
- `wp-init` now requires PHP >=7.4.
- Handlers for hook definitions are now grouped by calling hook and flushed late. Previously, each definition was hooked on itself, what might lead to a lot `add_action` calls.
### Fixed
- Improved integration with `wpdesk/wp-logs` and `wpdesk/wp-wpdesk-tracker`.
- Hook definitions are now resolved inside `plugins_loaded` hook to avoid classes not found when integrating with 3rd party code.

## [0.9.1] - 2024-08-13
### Fixed
- Fixed loading bindings.
- Improved compatibility with and without `wpdesk/wp-builder` library.
- Fixed typo in HPOS compatibility binding.

## [0.9.0] - 2024-08-08
### Added
- Initial library version. Still in development, missing integrations with some WP Desk components, but usable. Check README.md for details.
