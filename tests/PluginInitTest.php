<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use WPDesk\Init\Bootstrap\BootGate;
use WPDesk\Init\Init;

final class PluginInitTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'register_activation_hook' )->justReturn( true );
		Functions\when( 'register_deactivation_hook' )->justReturn( true );
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

	public function test_boot_rejects_list_style_module_configuration(): void {
		$plugin_file = $this->createTempFile(
			'list-style-modules.php',
			<<<'PHP'
<?php
/**
 * Plugin Name: List style modules
 * Version: 1.0.0
 */
PHP
		);

		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( 'class-string identifiers' );

		Init::setup(
			[
				'modules' => [
					\WPDesk\Init\Module\BuiltinModule::class,
				],
			]
		)->boot( $plugin_file );
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

	public function test_boot_registers_activation_and_deactivation_handlers(): void {
		$plugin_file = $this->createTempFile(
			'lifecycle-plugin.php',
			<<<'PHP'
<?php
/**
 * Plugin Name: Lifecycle plugin
 * Version: 1.0.0
 */
PHP
		);

		$activation_calls = 0;
		$deactivation_calls = 0;
		$activation_callback = null;
		$deactivation_callback = null;

		Functions\when( 'register_activation_hook' )->alias(
			static function ( string $file, $callback ) use ( $plugin_file, &$activation_callback ): void {
				\PHPUnit\Framework\Assert::assertSame( $plugin_file, $file );
				\PHPUnit\Framework\Assert::assertInstanceOf( \Closure::class, $callback );
				$activation_callback = $callback;
			}
		);

		Functions\when( 'register_deactivation_hook' )->alias(
			static function ( string $file, $callback ) use ( $plugin_file, &$deactivation_callback ): void {
				\PHPUnit\Framework\Assert::assertSame( $plugin_file, $file );
				\PHPUnit\Framework\Assert::assertInstanceOf( \Closure::class, $callback );
				$deactivation_callback = $callback;
			}
		);

		Init::setup(
			[
				'activation' => static function () use ( &$activation_calls ): void {
					$activation_calls++;
				},
				'deactivation' => static function () use ( &$deactivation_calls ): void {
					$deactivation_calls++;
				},
			]
		)->boot( $plugin_file );

		$this->assertInstanceOf( \Closure::class, $activation_callback );
		$this->assertInstanceOf( \Closure::class, $deactivation_callback );

		$activation_callback();
		$deactivation_callback();

		$this->assertSame( 1, $activation_calls );
		$this->assertSame( 1, $deactivation_calls );
	}

	public function test_boot_gate_can_stop_normal_hook_registration(): void {
		$plugin_file = $this->createTempFile(
			'gate-stop-plugin.php',
			<<<'PHP'
<?php
/**
 * Plugin Name: Gate stop plugin
 * Version: 1.0.0
 */
PHP
		);

		Functions\expect( 'add_action' )->never();

		Init::setup(
			[
				'gates' => [
					StopBootGate::class,
				],
			]
		)->boot( $plugin_file );

		$this->addToAssertionCount( 1 );
	}

	public function test_boot_gate_allows_normal_hook_registration_when_it_passes(): void {
		$plugin_file = $this->createTempFile(
			'gate-pass-plugin.php',
			<<<'PHP'
<?php
/**
 * Plugin Name: Gate pass plugin
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
				'gates' => [ PassBootGate::class ],
			]
		)->boot( $plugin_file );

		$this->addToAssertionCount( 1 );
	}

	public function test_boot_rejects_non_gate_classes(): void {
		$plugin_file = $this->createTempFile(
			'invalid-gate-class.php',
			<<<'PHP'
<?php
/**
 * Plugin Name: Invalid gate class
 * Version: 1.0.0
 */
PHP
		);

		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( 'must implement' );

		Init::setup(
			[
				'gates' => [
					\stdClass::class,
				],
			]
		)->boot( $plugin_file );
	}
}

final class StopBootGate implements BootGate {

	public function can_boot(): bool {
		return false;
	}

	public function on_failure(): void {
	}
}

final class PassBootGate implements BootGate {

	public function can_boot(): bool {
		return true;
	}

	public function on_failure(): void {
	}
}
