# WordPress plugin initializer

Boot your plugin with superpowers.

## Installation

To use this library in your project, add it to `composer.json`:

```sh
composer require wpdesk/wp-init
```

## Creating a Plugin

Preferred method of using this library exercise Object Oriented Programming and organizing your
actions and filters in a multiple classes, although it isn't the only way you can interact (and
benefit from this library).

The plugin initialization consists of the following steps:

1. Create a regular main plugin file, following [header requirements](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/)
1. Prepare DI container definitions for your services.
1. Declare all classes included in hook binding.

The above limits your main plugin file to a short and simple structure.

```php
<?php
/**
 * Plugin Name: Example Plugin
 */

use WPDesk\Init\Init;

require __DIR__ . '/vendor/autoload.php';

Init::setup('config.php')->boot();
```

### Plugin configuration

For plugin configuration, you may focus on succinct, declarative configuration.

[Supported configuration](docs/configuration.md):

```php
<?php

return [
	'hook_resources_path' => 'config/hook_providers',
	'services' => 'config/services.inc.php',
	'cache_path' => 'generated',

	'requirements' => [
		'plugins' => [
			'name' => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
		]
	],

	'plugin_class_name' => 'Example\Plugin',
];
```

## Usage with `wpdesk/wp-builder`

As a legacy support, it is possible to power up your existing codebase, which uses
`wpdesk/wp-builder` with this library capabilities, as autowired services.

The only change, you have to do (besides configuration of services) is adding _hookables_ as class
string, ready for handling by DI container:

```diff
- $this->add_hookable( new \WPDesk\Init\Provider\I18n() );
+ $this->add_hookable( \WPDesk\Init\Provider\I18n::class );
```

## Credits

This package is heavily inspired by Cedaro's [`wp-plugin`](https://github.com/cedaro/wp-plugin/)
and Alain Schlesser's [`basic-scaffold`](https://github.com/mwpd/basic-scaffold).

## License

Copyright (c) 2024 WPDesk

This library is licensed under MIT.
