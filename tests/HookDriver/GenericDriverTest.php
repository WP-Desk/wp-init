<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\HookDriver;

use WPDesk\Init\Binding\Binder;
use WPDesk\Init\Binding\Binder\HookableBinder;
use WPDesk\Init\Binding\Binder\ObservableBinder;
use WPDesk\Init\Binding\Definition;
use WPDesk\Init\HookDriver\GenericDriver;
use WPDesk\Init\Configuration\Configuration;
use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Tests\TestCase;

class GenericDriverTest extends TestCase {

	public function provider(): iterable {
		yield [
				'fake_binder' => new ObservableBinder(new class implements Binder {

					public function bind( Definition $def ): void {
					}
				}),
			function ( $binder ): void {
				$this->assertTrue( $binder->is_bound() );
			}
		];

// 		yield 'interrupted with stoppable binder' => [
// 			[
// 				'stoppable_binder' => new class implements StoppableBinder {
//
// 					public function should_stop(): bool {
// 						return true;
// 					}
//
// 					private $is_bound = false;
// 					public function bind(): void {
// 						$this->is_bound = true;
// 					}
//
// 					public function is_bound(): bool {
// 						return $this->is_bound;
// 					}
// 				},
// 				'fake_binder' => new ObservableBinder(new class implements Binder {
//
// 					public function bind(): void {
// 					}
// 				}),
// 			],
// 			function ( $binder ) {
// 				$this->assertTrue( $binder->is_bound() );
// 			}
// 		];
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

	public function test_register_hooks(): void {
		$binder = new ObservableBinder($this->getBinder());
		$driver = new GenericDriver(
			new ArrayDefinitions(['' => ['hook1', 'hook2']]),
			$binder
		);

		$driver->register_hooks();

		$this->assertEquals(2, $binder->binds_count());
	}

	private function getContainer( array $services ): ContainerInterface {
		return new class($services) implements ContainerInterface {
			private $services;
			public function __construct( $services ) {
				$this->services = $services;
			}

			public function get( $id ) {
				return $this->services[$id];
			}

			public function has(string $id ): bool {
				return isset( $this->services[$id] );
			}
		};
	}

	private function getBinder(): Binder {
		return new class implements Binder {

			public function bind( Definition $def ): void {
			}
		};
	}
}
