# wp-init changelog

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
