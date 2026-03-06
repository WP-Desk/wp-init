<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use WPDesk\Init\Init;

final class PluginInitTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'plugin_dir_path' )->alias( 'dirname' );
		Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.org/plugin/' );
		Functions\when( 'plugin_basename' )->returnArg();
		Functions\when( 'wp_get_environment_type' )->justReturn( 'production' );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_boot_registers_deferred_plugins_loaded_hook_for_hooks_config(): void {
		$plugin_file = $this->createTempFile(
			'example-plugin.php',
			<<<'PHP'
<?php
/**
 * Plugin Name: Example plugin
 * Version: 1.0.0
 */
PHP
		);

		Functions\expect( 'add_action' )
			->once()
			->with( 'plugins_loaded', \Mockery::type( \Closure::class ), -50 );

		Init::setup(
			[
				'hooks' => __DIR__ . '/Fixtures/hook-bindings',
			]
		)->boot( $plugin_file );

		$this->addToAssertionCount( 1 );
	}

	public function test_boot_supports_legacy_hook_resources_path_key(): void {
		$plugin_file = $this->createTempFile(
			'legacy-key-plugin.php',
			<<<'PHP'
<?php
/**
 * Plugin Name: Legacy key plugin
 * Version: 1.0.0
 */
PHP
		);

		Functions\expect( 'add_action' )
			->once()
			->with( 'plugins_loaded', \Mockery::type( \Closure::class ), -50 );

		Init::setup(
			[
				'hook_resources_path' => __DIR__ . '/Fixtures/hook-bindings',
			]
		)->boot( $plugin_file );

		$this->addToAssertionCount( 1 );
	}

	public function test_boot_rejects_invalid_module_config_values(): void {
		$plugin_file = $this->createTempFile(
			'invalid-module-config.php',
			<<<'PHP'
<?php
/**
 * Plugin Name: Invalid module config
 * Version: 1.0.0
 */
PHP
		);

		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( 'must be an array or null' );

		Init::setup(
			[
				'modules' => [
					\WPDesk\Init\Module\BuiltinModule::class => 'invalid',
				],
			]
		)->boot( $plugin_file );
	}

	public function test_boot_rejects_non_module_classes(): void {
		$plugin_file = $this->createTempFile(
			'invalid-module-class.php',
			<<<'PHP'
<?php
/**
 * Plugin Name: Invalid module class
 * Version: 1.0.0
 */
PHP
		);

		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( 'must implement' );

		Init::setup(
			[
				'modules' => [
					\stdClass::class => [],
				],
			]
		)->boot( $plugin_file );
	}
}
