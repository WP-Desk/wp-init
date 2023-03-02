<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Conditional;
use WPDesk\Init\ContainerAwareInterface;
use WPDesk\Init\HooksProvider;
use WPDesk\Init\Plugin;
use WPDesk\Init\PluginAwareInterface;

class PluginTest extends \PHPUnit\Framework\TestCase {

	public function test_should_register_hook_provider(): void {
		$plugin = new Plugin();

		$provider = new class implements HooksProvider {
			public $called = false;

			public function register_hooks(): void {
				$this->called = true;
			}
		};

		$plugin->register_hooks( $provider );

		$this->assertTrue( $provider->called );
	}

	public function test_should_not_register_failing_conditional_hook_provider(): void {
		$plugin = new Plugin();

		$provider = new class implements HooksProvider, Conditional {
			public $called = false;

			public function is_needed(): bool {
				return false;
			}

			public function register_hooks(): void {
				$this->called = true;
			}
		};

		$plugin->register_hooks( $provider );

		$this->assertFalse( $provider->called );
	}

	public function test_should_inject_plugin_on_provider(): void {
		$plugin = new Plugin();

		$provider = new class implements HooksProvider, PluginAwareInterface {
			public $called = false;
			public $plugin;

			public function set_plugin( Plugin $plugin ): void {
				$this->plugin = $plugin;
			}

			public function register_hooks(): void {
				$this->called = true;
			}
		};

		$plugin->register_hooks( $provider );

		$this->assertSame( $plugin, $provider->plugin );
	}

	public function test_should_inject_container_on_provider(): void {
		$plugin = new Plugin();
		$container = new class implements ContainerInterface {

			public function get( string $id ) {
				// TODO: Implement get() method.
			}

			public function has( string $id ): bool {
				return true;
			}
		};
		$plugin->set_container( $container );

		$provider = new class implements HooksProvider, ContainerAwareInterface {
			public $called = false;
			public $container;

			public function set_container( ContainerInterface $container ): void {
				$this->container = $container;
			}

			public function register_hooks(): void {
				$this->called = true;
			}
		};

		$plugin->register_hooks( $provider );

		$this->assertSame( $container, $provider->container );
	}

}