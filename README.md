# WordPress plugin initializer

## Installation

To use this library in your project, add it to `composer.json`:

```sh
composer require wpdesk/wp-init
```

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

Use declarative config for services, hooks, modules, and lifecycle handlers:

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

- `Hookable` classes are the default way to register WordPress hooks.
- Callable bindings are a narrow convenience for one-shot boot or lifecycle work.
- Modules are explicit opt-in bootstrap features with module-owned config.
- Boot gates stop the plugin before normal hook registration when viability checks fail.
- Activation and deactivation are handled explicitly through `activate` and `deactivate` config.

See [configuration](docs/configuration.md) for the full config shape and [legacy migration](docs/legacy.md) for `wp-builder` migration.

## Credits

This package is heavily inspired by Cedaro's [`wp-plugin`](https://github.com/cedaro/wp-plugin/)
and Alain Schlesser's [`basic-scaffold`](https://github.com/mwpd/basic-scaffold).

## License

Copyright (c) 2024 WPDesk

This library is licensed under MIT.
