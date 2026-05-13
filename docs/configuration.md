# Configuration

`wp-init` is configured with one PHP file that returns an array. Pass that file
to the public entrypoint:

```php
use WPDesk\Init\Init;

Init::setup( __DIR__ . '/config.php' )->boot();
```

`Init::setup()` also accepts a raw config array or a
`WPDesk\Init\Configuration\Configuration` instance. In normal plugins, prefer a
file so the main plugin file stays small.

## Minimal Config

```php
<?php

return [
	'services' => __DIR__ . '/config/services.php',
	'hooks' => __DIR__ . '/config/hooks',
];
```

## Full Shape

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

Only add keys that the plugin needs. `services`, `hooks`, `modules`, `gates`,
`activate`, and `deactivate` may all be omitted.

## `services`

Path or list of paths to PHP-DI definition files. Relative paths are resolved
from the plugin directory.

`wp-init` loads prefixed PHP-DI helper functions during bootstrap. Use the
`WPDesk\Init\DI` namespace in service definition files:

```php
<?php

use Psr\Log\LoggerInterface;
use Vendor\Plugin\Infrastructure\FileLogger;
use function WPDesk\Init\DI\autowire;
use function WPDesk\Init\DI\get;

return [
	LoggerInterface::class => autowire( FileLogger::class )
		->constructorParameter( 'path', get( 'plugin.log_path' ) ),
	'plugin.log_path' => __DIR__ . '/../var/plugin.log',
];
```

## `hooks`

Path to a hook definition file or a directory of hook definition files. Relative
paths are resolved from the plugin directory.

When `hooks` points to a directory, filenames map to WordPress hook names:

- `plugins_loaded.php` binds entries to `plugins_loaded`
- `woocommerce_init.php` binds entries to `woocommerce_init`
- `index.php` is loaded as-is; numeric entries run during the deferred binding
  pass, and string keys are treated as explicit hook names

Hook definitions are loaded on `plugins_loaded` with priority `-50`. This
delays class checks until other plugins have had a chance to load their
interfaces and classes.

Use `Hookable` classes for normal WordPress integrations:

```php
<?php

use Vendor\Plugin\Hooks\LoadTextdomain;
use Vendor\Plugin\Hooks\RegisterAdminPage;

return [
	LoadTextdomain::class,
	RegisterAdminPage::class,
];
```

A hookable class must implement `WPDesk\Init\Binding\Hookable`:

```php
use WPDesk\Init\Binding\Hookable;

final class RegisterAdminPage implements Hookable {

	public function hooks(): void {
		add_action( 'admin_menu', [ $this, 'register' ] );
	}

	public function register(): void {
		// Register the page.
	}
}
```

Use callable bindings only for small one-shot actions. Callable parameters must
be single named class or interface types that exist in the container. Built-in
types, union types, and unresolved entries are rejected.

```php
<?php

use Vendor\Plugin\Migrations;

return [
	static function ( Migrations $migrations ): void {
		$migrations->migrate();
	},
];
```

`index.php` may also return an explicit hook map:

```php
<?php

return [
	'init' => [
		\Vendor\Plugin\Hooks\RegisterPostTypes::class,
	],
	'admin_init' => [
		static function ( \Vendor\Plugin\Admin\Setup $setup ): void {
			$setup->run();
		},
	],
];
```

For custom WordPress priorities, use a `Hookable` class and pass the priority to
`add_action()` inside `hooks()`.

## `modules`

Modules are explicit opt-in bootstrap features. The array key is the module
class name and the value is that module's config.

Rules:

- keys must be module class strings
- values must be arrays or `null`
- `null` is normalized to `[]`
- array order is preserved
- every configured class must implement `WPDesk\Init\Module\Module`

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

Module-specific config lives under that module entry. Root config is not used as
a fallback for module options.

### WP Desk Free Preset

Install:

```sh
composer require wpdesk/wp-init-plugin-free
```

Configure all three free modules:

```php
use WPDesk\Init\PluginFree\FreePluginModule;
use WPDesk\Init\PluginFree\RequirementsModule;
use WPDesk\Init\PluginFree\WPDeskTrackerModule;

'modules' => [
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
]
```

`FreePluginModule` is a marker module. It verifies that
`RequirementsModule` and `WPDeskTrackerModule` are configured.

`RequirementsModule` accepts only `requirements` and requires it to be a
non-empty array.

`WPDeskTrackerModule` accepts only `shops`. `shops.default` is required and each
shop value must be a valid URL.

### WP Desk Paid Preset

Install:

```sh
composer require wpdesk/wp-init-plugin-paid
```

Configure the paid marker, the free marker, free modules, and the license
module:

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

`PaidPluginModule` verifies that `FreePluginModule` and
`WPDeskLicenseModule` are configured.

`WPDeskLicenseModule` accepts only `product_id` and `shops`.
`product_id` must be a non-empty string. `shops.default` is required and each
shop value must be a valid URL.

## `gates`

Explicit boot gates for plugin-specific viability checks.

Rules:

- values must be gate class strings
- gate classes must implement `WPDesk\Init\Bootstrap\BootGate`
- gates are resolved through the container
- gates run after the container is initialized and before normal hook bindings
  are registered

Example:

```php
use WPDesk\Init\Bootstrap\BootGate;

final class WooCommerceVersionGate implements BootGate {

	public function can_boot(): bool {
		return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '8.0', '>=' );
	}

	public function on_failure(): void {
		add_action(
			'admin_notices',
			static function (): void {
				echo '<div class="notice notice-error"><p>WooCommerce 8.0 or newer is required.</p></div>';
			}
		);
	}
}
```

Use gates when the whole plugin should remain active but skip normal boot, for
example when a required dependency is absent or incompatible.

If any gate fails:

- the failing gate handles its own failure behavior through `on_failure()`
- normal plugin boot stops
- `activate` and `deactivate` callbacks remain registered

Requirements checks are provided by
`WPDesk\Init\PluginFree\RequirementsModule`; that module contributes its own
gate internally.

## `activate`

Explicit activation handlers. Accepts one binding or an array of bindings.

Use this for work that should run only on plugin activation, such as database
migrations or initial option setup:

```php
'activate' => [
	static function ( \Vendor\Plugin\Migrations $migrations ): void {
		$migrations->migrate();
	},
]
```

Allowed entries use the same binding validation as normal hook definitions:
callables with container-resolved object parameters or hookable class strings.
Prefer callables for lifecycle work. A hookable class entry has its `hooks()`
method called when the lifecycle event fires.

## `deactivate`

Explicit deactivation handlers. Accepts the same shapes as `activate`.

Use this for cleanup that must run only on plugin deactivation:

```php
'deactivate' => [
	static function ( \Vendor\Plugin\Cleanup $cleanup ): void {
		$cleanup->run();
	},
]
```

## `cache_path`

Directory used for cached plugin header data and the compiled DI container.
Defaults to `generated`.

Relative paths are resolved from the plugin directory. In production, this
directory should be writable. If container compilation cannot be used safely,
`wp-init` falls back to a live container.

## `environment`

Controls bootstrap mode. Recommended values are:

- `production`
- `development`

If omitted, `wp-init` resolves environment in this order:

1. explicit config value
2. `wp_get_environment_type()`
3. plugin version containing `dev`
4. `production`

Only `development` disables container compilation. Any other non-empty value is
treated as production-like by the bootstrap runtime.

## `debug`

Controls diagnostic verbosity. If omitted, `development` implies debug mode.

`debug` does not make invalid config acceptable. Invalid config still fails
loudly.

## Boot Order

The runtime boot order is:

1. load config
2. parse or load cached plugin header data
3. resolve modules
4. build the container
5. register activation and deactivation hooks
6. run boot gates
7. register normal hook bindings through the deferred hook driver

Normal hook bindings are deferred until `plugins_loaded` priority `-50`.

## Legacy Builder Module

Legacy support is explicit. To enable `wpdesk/wp-builder` integration, add
`LegacyBuilderModule` to `modules` and configure it directly:

```php
'modules' => [
	\WPDesk\Init\Module\LegacyBuilderModule::class => [
		'plugin_class_name' => \Vendor\Plugin\LegacyPlugin::class,
		'product_id' => 'my-product',
		'shops' => [ 'pl', 'com' ],
	],
]
```

`plugin_class_name` is required. `product_id` and `shops` are optional legacy
values passed to `WPDesk_Plugin_Info`.

See [legacy migration](legacy.md) for the migration path.
