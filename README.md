# WordPress plugin initializer

Bootstrap for your plugins.

## Installation

To use this library in your project, add it to `composer.json`:

```sh
composer require wpdesk/wp-init
```

## Creating a Plugin

A plugin is a simple object created to help bootstrap functionality by allowing you to easily 
retrieve plugin information, reference internal files and URLs, and, most importantly, register 
hooks.

```php
<?php
/**
 * Plugin Name: Example Plugin
 */

use WPDesk\Init\PluginInit;

require __DIR__ . '/vendor/autoload.php';

$plugin = (new PluginInit())->init();
```

`$plugin` is an instance of `Plugin`, which provides a basic API to access information about the plugin.

You can find more elaborate examples for plugin configuration in `tests/Fixtures/` directory.

### Plugin configuration

Although, you don't need additional configuration to begin work with your plugin in OOP manner, 
most of the time you will need a few more steps to make the most of plugin initializer.

#### Using dependency injection container

Paired with `php-di/php-di`, you are able to take leverage of dependency injection in your 
WordPress plugin. `wp-init` is ready to pick up all of your hook provider classes from special 
container entry named `hooks`. Any class registered in this array will be called during 
`plugins_loaded` hook.

```shell
composer require php-di/php-di
```

```php
<?php
/**
 * Plugin Name: Example Plugin
 */

use WPDesk\Init\PluginInit;

require __DIR__ . '/vendor/autoload.php';

$plugin = (new PluginInit())
    ->add_container_definitions([
        'hooks' => [
            \DI\autowire( \Example\PostType\BookPostType::class )
        ]
    ])
    ->init();
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
    ->init();
```

## Hook Providers

Related functionality can be encapsulated in a class called a "hook provider" that's registered when bootstrapping the plugin.

Hook providers allow you to encapsulate related functionality, maintain state without using globals, namespace methods without prefixing functions, limit access to internal methods, and make unit testing easier.

For an example, the `WPDesk\Init\Provider\I18n` class is a default hook provider that automatically 
loads the text domain so the plugin can be translated.

The only requirement for a hook provider is that it should implement the `HookProvider` 
interface by defining a method called `register_hooks()`.

Hook providers are registered with the main plugin instance by calling `Plugin::register_hooks()` like this:

```php
<?php
$plugin->register_hooks(
     new \Cedaro\WP\Plugin\Provider\I18n(),
     new \Example\PostType\BookPostType()
);
```

The `BookPostType` provider might look something like this:

```php
<?php
namespace Example\PostType;

use WPDesk\Init\Provider\AbstractHookProvider;

class BookPostType extends AbstractHookProvider {
	const POST_TYPE = 'book';

	public function register_hooks() {
		$this->add_action( 'init', 'register_post_type' );
		$this->add_action( 'init', 'register_meta' );
	}

	protected function register_post_type() {
		register_post_type( static::POST_TYPE, $this->get_args() );
	}

	protected function register_meta() {
		register_meta( 'post', 'isbn', array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		) );
	}

	protected function get_args() {
		return array(
			'hierarchical'      => false,
			'public'            => true,
			'rest_base'         => 'books',
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'show_in_rest'      => true,
		);
	}
}
```
<!--
## Protected Hook Callbacks

In WordPress, it's only possible to use public methods of a class as hook callbacks, but in the `BookPostType` hook provider above, the callbacks are protected methods of the class.

Locking down the API like that is possible using the `HooksTrait` [developed by John P. Bloch](https://github.com/johnpbloch/wordpress-dev).
-->

## Plugin Awareness

A hook provider may implement the `PluginAwareInterface` to automatically receive a reference to the plugin when its hooks are registered.

For instance, in this class the `enqueue_assets()` method references the internal `$plugin` property to retrieve the URL to a JavaScript file in the plugin.

```php
<?php
namespace Structure\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;

class Assets extends AbstractHookProvider {
	public function register_hooks() {
		$this->add_action( 'wp_enqueue_scripts', 'enqueue_assets' );
	}

	protected function enqueue_assets() {
		wp_enqueue_script(
			'structure',
			$this->plugin->get_url( 'assets/js/structure.js' )
		);
	}
}
```

Another example is the `I18n` provider mentioned earlier. It receives a reference to the plugin object so that it can use the plugin's base name and slug to load the text domain.

Classes that extend `AbstractHookProvider` are automatically "plugin aware."

## Credits

This package is heavily inspired by Cedaro's [`wp-plugin`](https://github.com/cedaro/wp-plugin/) 
and Alain Schlesser's [`basic-scaffold`](https://github.com/mwpd/basic-scaffold).

## Roadmap

1. Add support for path based hook providers discovery similar to Symfony's [controllers resolving](https://github.com/symfony/demo/blob/3787b9f71f6bee24f1ed0718b9a808d824008776/config/routes.yaml#L15-L17)
1. Improve `wpdesk/wp-basic-requirements` library. This is not related directly to this project, 
   but internals could be rewritten.
1. Scrap plugin data from plugin comment
1. Allow hooks to be called from private and protected methods (in PHP <8.1)
1. Support *bundles* of hook providers. This should be easy to extend plugin capabilities with 
   shared functions, preserving minimal init system

## License

Copyright (c) 2023 WPDesk

This library is licensed under MIT.
