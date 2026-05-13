## Configuration

`wp-init` uses one declarative config file. The canonical `1.0` shape is:

```php
<?php

use WPDesk\Init\Module\LegacyBuilderModule;
use WPDesk\Init\PluginFree\FreePluginModule;
use WPDesk\Init\PluginFree\RequirementsModule;
use WPDesk\Init\PluginFree\WPDeskTrackerModule;

return [
	'services' => __DIR__ . '/config/services.php',
	'hooks' => __DIR__ . '/config/hooks',
	'cache_path' => 'generated',
	'environment' => 'production',
	'debug' => false,
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
		LegacyBuilderModule::class => [
			'plugin_class_name' => \Vendor\Plugin\LegacyPlugin::class,
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

## `services`

Path or list of paths to PHP-DI definition files. Paths may be relative or absolute.

`wp-init` loads its own prefixed PHP-DI helper functions during bootstrap, so plugin bootstrap files do not need to load helper helpers manually.

## `hooks`

Path to a file or directory with hook definitions.

Files are mapped to hooks by filename:

- `plugins_loaded.php` binds on `plugins_loaded`
- `woocommerce_init.php` binds on `woocommerce_init`
- `index.php` is flushed immediately during the deferred binding pass

Example hook file:

```php
<?php

return [
	\Vendor\Plugin\Hooks\LoadTextdomain::class,
	static function ( \Vendor\Plugin\Migrations $migrations ): void {
		$migrations->migrate();
	},
];
```

Hookables are the normal case. Callables are supported as a narrow bridge and are resolved through the container.

## `modules`

Modules are explicit opt-in bootstrap features. The key is the module class name and the value is its config.

Rules:

- keys must be module class strings
- values must be arrays or `null`
- `null` is normalized to `[]`
- array order is preserved

Example:

```php
'modules' => [
	\WPDesk\Init\PluginFree\RequirementsModule::class => [
		'requirements' => [
			'php' => '>=8.1',
		],
	],
	\WPDesk\Init\PluginFree\WPDeskTrackerModule::class => [
		'shops' => [
			'default' => 'https://wpdesk.net',
		],
	],
]
```

Module-specific config lives under that module entry. Root config is not used as a fallback for module options.

WP Desk plugin presets are marker modules. They do not introduce another config shape; concrete modules are still configured by hand under their own class names.

```php
use WPDesk\Init\PluginFree\FreePluginModule;
use WPDesk\Init\PluginFree\RequirementsModule;
use WPDesk\Init\PluginFree\WPDeskTrackerModule;
use WPDesk\Init\PluginPaid\PaidPluginModule;
use WPDesk\Init\PluginPaid\WPDeskLicenseModule;

'modules' => [
	PaidPluginModule::class => null,
	FreePluginModule::class => null,
	RequirementsModule::class => [
		'requirements' => [
			'php' => '>=8.1',
		],
	],
	WPDeskTrackerModule::class => [
		'shops' => [
			'default' => 'https://wpdesk.net',
			'pl_PL' => 'https://www.wpdesk.pl',
		],
	],
	WPDeskLicenseModule::class => [
		'product_id' => 'my-product',
		'shops' => [
			'default' => 'https://wpdesk.net',
			'pl_PL' => 'https://www.wpdesk.pl',
		],
	],
]
```

## `gates`

Explicit boot gates for plugin-specific viability checks.

Rules:

- values must be gate class strings
- gate classes must implement `WPDesk\Init\Bootstrap\BootGate`
- gates run after the container is initialized and before normal hook bindings are registered

Use gates when the whole plugin must remain active but not boot, for example because a required dependency version is not compatible. Gates may render notices in `on_failure()`.

Requirements checks stay ergonomic through `WPDesk\Init\PluginFree\RequirementsModule`; that module provides its own gate internally.

## `activate`

Explicit activation handlers. Accepts a single callable/class definition or an array of definitions.

Supported entries are the same strict binding shapes used for normal bootstrap bindings:

- hookable class strings
- callables with container-resolvable object parameters only

Use this for work such as database migrations or setup that should happen on plugin activation.

## `deactivate`

Explicit deactivation handlers. Accepts the same shapes as `activate`.

Use this for cleanup work that must happen on plugin deactivation.

## `cache_path`

Directory used for cached plugin header data and compiled DI container. Defaults to `generated`.

## `environment`

Controls bootstrap mode. Supported values are:

- `production`
- `development`

If omitted, `wp-init` resolves environment in this order:

1. explicit config value
2. `wp_get_environment_type()`
3. plugin version containing `dev`
4. `production`

`development` disables container compilation.

## `debug`

Controls diagnostic verbosity. If omitted, `development` implies debug mode.

`debug` does not make invalid config acceptable. Invalid config still fails loudly.

## Boot Gates

Boot gates may be configured directly through `gates` or provided by modules. They run after the container is initialized and before normal hook bindings are registered.

If any gate fails:

- the gate handles its own failure behavior
- normal plugin boot stops
- `activate` and `deactivate` callbacks remain registered

## Legacy Builder Module

Legacy support is explicit. To enable `wpdesk/wp-builder` integration, add `LegacyBuilderModule` to `modules` and configure it directly:

```php
'modules' => [
	\WPDesk\Init\Module\LegacyBuilderModule::class => [
		'plugin_class_name' => \Vendor\Plugin\LegacyPlugin::class,
		'product_id' => 'my-product',
		'shops' => [ 'pl', 'com' ],
	],
]
```

See [legacy migration](legacy.md) for the migration path.
