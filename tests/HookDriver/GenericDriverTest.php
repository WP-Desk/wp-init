<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\HookDriver;

use Brain\Monkey;
use Brain\Monkey\Functions;
use WPDesk\Init\Binding\Binder;
use WPDesk\Init\Binding\Binder\ObservableBinder;
use WPDesk\Init\Binding\Definition;
use WPDesk\Init\HookDriver\GenericDriver;
use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Tests\TestCase;

class GenericDriverTest extends TestCase {

	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
		Monkey\tearDown();
	}

	public function test_register_no_hooks(): void {
		$binder = new ObservableBinder($this->getBinder());
		$driver = new GenericDriver(
			new ArrayDefinitions([]),
			$binder
		);

		$driver->register_hooks();

		$this->assertEquals(0, $binder->binds_count());
	}

	public function test_register_hooks_deferred_until_plugins_loaded(): void {
		$registrations = [];
		$plugins_loaded_callback = null;
		$hook_callback = null;

		Functions\when( 'add_action' )->alias(
			static function ( $hook, $callback, $priority = 10 ) use ( &$registrations, &$plugins_loaded_callback, &$hook_callback ): void {
				$registrations[] = [
					'hook' => $hook,
					'callback' => $callback,
					'priority' => $priority,
				];

				if ( $hook === 'plugins_loaded' ) {
					$plugins_loaded_callback = $callback;
				}

				if ( $hook === 'hook1' ) {
					$hook_callback = $callback;
				}
			}
		);

		$binder = new ObservableBinder($this->getBinder());
		$driver = new GenericDriver(
			new ArrayDefinitions(
				[
					static function (): void {
					},
					'hook1' => static function (): void {
					},
				]
			),
			$binder
		);

		$driver->register_hooks();

		$this->assertCount( 1, $registrations );
		$this->assertSame( 'plugins_loaded', $registrations[0]['hook'] );
		$this->assertInstanceOf( \Closure::class, $plugins_loaded_callback );
		$this->assertEquals( 0, $binder->binds_count() );

		$plugins_loaded_callback();

		$this->assertCount( 2, $registrations );
		$this->assertSame( 'hook1', $registrations[1]['hook'] );
		$this->assertInstanceOf( \Closure::class, $hook_callback );
		$this->assertEquals( 1, $binder->binds_count() );

		$hook_callback();

		$this->assertEquals( 2, $binder->binds_count() );
	}

	private function getBinder(): Binder {
		return new class implements Binder {

			public function bind( Definition $def ): void {
			}
		};
	}
}
