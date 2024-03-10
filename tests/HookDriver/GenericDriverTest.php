<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\HookDriver;

use WPDesk\Init\Binding\HookBinder;
use WPDesk\Init\Binding\ObservableBinder;
use WPDesk\Init\HookDriver\GenericDriver;
use WPDesk\Init\Configuration\Configuration;
use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Binding\StoppableBinder;
use WPDesk\Init\Tests\TestCase;

class GenericDriverTest extends TestCase {

	public function provider(): iterable {
		yield [
			[
				'fake_binder' => new ObservableBinder(new class implements HookBinder {

					public function bind(): void {
					}
				}),
			],
			function ( $binder ) {
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
// 				'fake_binder' => new ObservableBinder(new class implements HookBinder {
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

	/** @dataProvider provider */
	public function test_register_hooks( array $hook_bindings, callable $assertion ): void {
		$driver = new GenericDriver( new ArrayDefinitions(array_keys($hook_bindings)) );
		$driver->register_hooks( new Configuration([]), $this->getContainer($hook_bindings) );


		foreach ( $hook_bindings as $hook => $binder ) {
			$assertion( $binder );
		}
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
}
