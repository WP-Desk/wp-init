<?php
declare( strict_types=1 );

namespace WPDesk\Init\Binding\Loader;

use WPDesk\Init\Binding\DefinitionFactory;
use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\Plugin\Plugin;

class ArrayBindingLoader implements BindingDefinitions {

	/** @var array */
	private $bindings;

	/**
	 * @var DefinitionFactory
	 */
	private $factory;


	public function __construct( array $bindings, DefinitionFactory $factory ) {
		$this->bindings = $bindings;
		$this->factory  = $factory;
	}

	public function load(): iterable {
		yield from $this->normalize( $this->bindings );
	}

	private function normalize( $bindings ) {
		$normalized = [];
		foreach ( $bindings as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $unit ) {
					yield $this->factory->create( $unit, is_int( $key ) ? null : $key );
				}
			} else {
				yield $this->factory->create( $value, is_int( $key ) ? null : $key );
			}
		}
	}
}
