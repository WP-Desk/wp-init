# WordPress plugin initializer

`wp-init` keeps WordPress plugin bootstrapping in one small entrypoint and one
declarative config file. It wires PHP-DI services, hookable classes, optional
modules, boot gates, and activation/deactivation handlers.

## Installation

For the core bootstrap library:

```sh
composer require wpdesk/wp-init
```

For WP Desk free plugin defaults, install the free preset package instead:

```sh
composer require wpdesk/wp-init-plugin-free
```

For WP Desk paid plugin defaults, install the paid preset package:

```sh
composer require wpdesk/wp-init-plugin-paid
```

The preset packages install `wpdesk/wp-init` and the packages required by their
modules.

## Bootstrapping a plugin

Keep the main plugin file minimal:

```php
<?php
/**
 * Plugin Name: Example Plugin
 */

use WPDesk\Init\Init;

require __DIR__ . '/vendor/autoload.php';

Init::setup( __DIR__ . '/config.php' )->boot();
```

The config file may be minimal:

```php
<?php

return [
	'services' => __DIR__ . '/config/services.php',
	'hooks' => __DIR__ . '/config/hooks',
];
```

WP Desk plugin presets are enabled explicitly through modules:

```php
<?php

use WPDesk\Init\PluginFree\FreePluginModule;
use WPDesk\Init\PluginFree\RequirementsModule;
use WPDesk\Init\PluginFree\WPDeskTrackerModule;

return [
	'services' => __DIR__ . '/config/services.php',
	'hooks' => __DIR__ . '/config/hooks',
	'cache_path' => 'generated',
	'environment' => 'production',
	'modules' => [
		FreePluginModule::class => null,
		RequirementsModule::class => [
			'requirements' => [
				'plugins' => [
					[
						'name' => 'woocommerce/woocommerce.php',
						'nice_name' => 'WooCommerce',
					],
				],
			],
		],
		WPDeskTrackerModule::class => [
			'shops' => [
				'default' => 'https://wpdesk.net',
				'pl_PL' => 'https://www.wpdesk.pl',
			],
		],
	],
	'gates' => [
		\Vendor\Plugin\Infrastructure\CustomCompatibilityGate::class,
	],
	'activate' => [
		static function ( \Vendor\Plugin\Migrations $migrations ): void {
			$migrations->migrate();
		},
	],
	'deactivate' => [
		\Vendor\Plugin\Hooks\CleanupOnDeactivate::class,
	],
];
```

Main concepts:

- `Hookable` classes are the primary way to register WordPress hooks.
- Callable bindings are for small one-shot boot or lifecycle work.
- Modules are explicit opt-in bootstrap features with module-owned config.
- Boot gates stop the plugin before normal hook registration when viability checks fail.
- Activation and deactivation are handled explicitly through `activate` and `deactivate` config.

See [configuration](docs/configuration.md) for the full config shape,
[legacy migration](docs/legacy.md) for `wp-builder` migration, and
[prefixing](docs/prefixing.md) for PHP-Scoper setup.

## Credits

This package is heavily inspired by Cedaro's [`wp-plugin`](https://github.com/cedaro/wp-plugin/)
and Alain Schlesser's [`basic-scaffold`](https://github.com/mwpd/basic-scaffold).

## License

Copyright (c) 2024 WPDesk

This library is licensed under MIT.
