<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Binding;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Binder\CallableBinder;
use WPDesk\Init\Binding\Definition\CallableDefinition;
use WPDesk\Init\Binding\Exception\InvalidCallableBinding;
use WPDesk\Init\Tests\TestCase;

final class CallableBinderTest extends TestCase {

	public function test_it_invokes_callable_with_container_resolved_dependency(): void {
		$called = false;
		$service = new \stdClass();

		$binder = new CallableBinder( $this->container( [ \stdClass::class => $service ] ) );
		$binder->bind(
			new CallableDefinition(
				static function ( \stdClass $dependency ) use ( &$called, $service ): void {
					$called = $dependency === $service;
				}
			)
		);

		$this->assertTrue( $called );
	}

	public function test_it_rejects_builtin_parameter_types(): void {
		$binder = new CallableBinder( $this->container( [] ) );

		$this->expectException( InvalidCallableBinding::class );
		$this->expectExceptionMessage( 'cannot use builtin type "string"' );

		$binder->bind(
			new CallableDefinition(
				static function ( string $dependency ): void {
				}
			)
		);
	}

	public function test_it_rejects_missing_container_entries(): void {
		$binder = new CallableBinder( $this->container( [] ) );

		$this->expectException( InvalidCallableBinding::class );
		$this->expectExceptionMessage( 'which is not available' );

		$binder->bind(
			new CallableDefinition(
				static function ( \stdClass $dependency ): void {
				}
			)
		);
	}

	private function container( array $services ): ContainerInterface {
		return new class( $services ) implements ContainerInterface {

			/** @var array<string, object> */
			private array $services;

			/**
			 * @param array<string, object> $services
			 */
			public function __construct( array $services ) {
				$this->services = $services;
			}

			public function get( $id ) {
				return $this->services[ $id ];
			}

			public function has( string $id ): bool {
				return array_key_exists( $id, $this->services );
			}
		};
	}
}
