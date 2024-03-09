# Configuration

`wp-init` relies on declarative configuration, which encapsulates process of attaching hooks to
WordPress life cycle and provides some additional features, like filling DI container with your
services definitions.

## `hook_resources_path`

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

## `services`

As you add more services with increasing complexity, you will need to provide some kind of
definitions for a DI container to create objects. Pass a path to a file, which will hold such
definitions. Refer to [php-di documentation](https://php-di.org/doc/definitions.html) for more
information on such file content.

> Warning
>
> If you are using _shortcut_ functions from `php-di/php-di` (e.g. `DI\autowire`, `DI\create`), you
> must load them first. Add `require __DIR__ . '/vendor_prefixed/php-di/php-di/src/functions.php';`
> to your plugin file.

## `cache_path`

Plugin header data and compiled DI container is cached in a directory specified by this
setting. Defaults to `generated`.

## `requirements`

**This setting only works when `wpdesk/wp-basic-requirements` is installed.**

Enables your plugin to check an environment requirement before instantiation, e.g. PHP version or
active plugins. Refer to [wp-basic-requirements documentation](https://gitlab.wpdesk.dev/wpdesk/wp-basic-requirements)
for more information on setting structure.

## `plugin_class_name`

**This setting only works when `wpdesk/wp-builder` is installed.**

When a plugin is used in [legacy mode](legacy.md), `plugin_class_name` is used to create an instance
of main plugin class. This setting is required to enable legacy mode. Despite that,
`WPDesk\Init\Plugin\Plugin` is still accessible to your services.
