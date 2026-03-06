# Configuration

`wp-init` relies on declarative configuration, which encapsulates process of attaching hooks to
WordPress life cycle and provides some additional features, like filling DI container with your
services definitions.

## `hooks`

Pass a path to the file/directory with your hook actions. Configuration accepts any valid path
string, relative or absolute, either `hook_providers` or `__DIR__ . '/hook_providers/plugins_loaded.php'`

Files are mapped to hooks by name, so `woocommerce_init.php` is registered inside `woocommerce_init`
action. The exception is `index.php` file which is flushed immediately.

Example of a hook resource content:

```php
<?php
// plugins_loaded.php

return [
	MyCoolTitleChanger::class,
	AnotherHookAction::class,
	function ( Migarator $migrator ) {
		// You can even use a closure, to execute simple actions.
		// Arguments are injected by DI container.
		$migrator->migrate();
	}
];
```

`hook_resources_path` is still accepted as a compatibility key, but `hooks` is the canonical name for `1.0`.

## `services`

As you add more services with increasing complexity, you will need to provide some kind of
definitions for a DI container to create objects. Pass a path to a file, which will hold such
definitions. Refer to [php-di documentation](https://php-di.org/doc/definitions.html) for more
information on such file content.

`wp-init` loads its own prefixed helper functions for PHP-DI during bootstrap, so plugin bootstrap files do not need to load helper functions manually.

## `cache_path`

Plugin header data and compiled DI container is cached in a directory specified by this
setting. Defaults to `generated`.

## `modules`

Modules are configured as an associative array where the key is a module class name and the value is its configuration array.

Example:

```php
<?php

use WPDesk\Init\Module\RequirementsModule;

return [
	'modules' => [
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
	],
];
```

`null` is also accepted as a module configuration value and is normalized to an empty array.

## `environment`

Controls bootstrap mode. Supported values are `production` and `development`.

If omitted, `wp-init` resolves the environment in this order:

1. explicit config value
2. `wp_get_environment_type()`
3. plugin version containing `dev`
4. fallback to `production`

## `debug`

Enables additional diagnostics. When omitted, `development` environment implies debug mode.

## Requirements and Legacy Modules

The old root-level `requirements` and `plugin_class_name` keys are still accepted for compatibility.

Preferred `1.0` direction:

- requirements go into `RequirementsModule` config
- legacy builder support is enabled by adding `LegacyBuilderModule` to `modules`

## `plugin_class_name`

**This setting only works when `wpdesk/wp-builder` is installed.**

When a plugin is used in [legacy mode](legacy.md), `plugin_class_name` is used to create an instance
of main plugin class. This setting is required to enable legacy mode. Despite that,
`WPDesk\Init\Plugin\Plugin` is still accessible to your services.
