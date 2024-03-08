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

use WPDesk\Init;

require __DIR__ . '/vendor/autoload.php';

(new Init('config.php'))->boot();
```

### Plugin configuration

For plugin configuration, you may focus on declarative configuration.

Supported configuration:

```php
<?php

return [
	'services' => 'config/services.inc.php',
	'hook_binding' => [],
	'cache_path' => 'generated',
	'requirements' => [],

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

#### Target environment requirements

`wp-init` also integrates with `wpdesk/wp-basic-requirements` to validate target environment and
detect, whether your plugin can actually instantiate. With this addition, if your plugin fails
to positively validate environment, your hooks are not triggered, and the website admin is
provided with actionable message.

```shell
composer require wpdesk/wp-basic-requirements
```

```php
<?php
/**
 * Plugin Name: Example Plugin
 */

use WPDesk\Init\PluginInit;

require __DIR__ . '/vendor/autoload.php';

$plugin = (new PluginInit())
    ->set_requirements([
        'wp' => '6.0',
        'php' => '7.2'
    ])
    ->boot();
```

## Credits

This package is heavily inspired by Cedaro's [`wp-plugin`](https://github.com/cedaro/wp-plugin/)
and Alain Schlesser's [`basic-scaffold`](https://github.com/mwpd/basic-scaffold).

## Roadmap

1. Add support for path based hook providers discovery similar to Symfony's [controllers resolving](https://github.com/symfony/demo/blob/3787b9f71f6bee24f1ed0718b9a808d824008776/config/routes.yaml#L15-L17)
1. Improve `wpdesk/wp-basic-requirements` library. This is not related directly to this project, but internals could be rewritten.
1. Scrap plugin data from plugin comment
1. Support *bundles* of hook providers. This should be easy to extend plugin capabilities with shared functions, preserving minimal init system

## License

Copyright (c) 2024 WPDesk

This library is licensed under MIT.
