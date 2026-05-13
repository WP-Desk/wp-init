# Migration from `wpdesk/wp-builder`

Legacy support exists as an explicit module. It is a migration bridge, not the default bootstrap model.

## Enable the legacy module

Install `wpdesk/wp-builder` and configure `LegacyBuilderModule` in `modules`:

```php
<?php

use WPDesk\Init\Module\LegacyBuilderModule;

return [
	'services' => __DIR__ . '/config/services.php',
	'hooks' => __DIR__ . '/config/hooks',
	'modules' => [
		LegacyBuilderModule::class => [
			'plugin_class_name' => \Vendor\Plugin\LegacyPlugin::class,
		],
	],
];
```

`plugin_class_name` is required. `product_id` and `shops` may also be passed
when the legacy plugin still reads those values from `WPDesk_Plugin_Info`:

```php
LegacyBuilderModule::class => [
	'plugin_class_name' => \Vendor\Plugin\LegacyPlugin::class,
	'product_id' => 'my-product',
	'shops' => [ 'pl', 'com' ],
],
```

There is no root-level legacy mode switch.

## What changes in hook registration

Move hookables to class strings so `wp-init` can resolve them through the container:

```diff
- $this->add_hookable( new \Vendor\Plugin\Hooks\LoadTextdomain() );
+ $this->add_hookable( \Vendor\Plugin\Hooks\LoadTextdomain::class );
```

That is the main behavioral change: hookables become container-managed. If a
hookable needs constructor arguments, define them in the `services` file.

## Suggested migration path

1. Keep the existing plugin structure.
2. Introduce `wp-init` with `LegacyBuilderModule`.
3. Move hookables to class strings.
4. Move service wiring to `services.php`.
5. Move requirements, tracker, and licensing concerns into explicit modules when
   the plugin is ready.
6. Remove `wp-builder` usage when the plugin no longer needs the legacy adapter.

## What legacy support is for

Use the legacy module when you need to bootstrap an existing `wp-builder` plugin without rewriting its whole structure in one step.

Do not use it for new plugins. New plugins should bootstrap directly with `Init`, `hooks`, `services`, and explicit modules.
