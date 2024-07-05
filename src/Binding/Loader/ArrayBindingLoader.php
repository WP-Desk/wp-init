<?php
declare( strict_types=1 );

namespace WPDesk\Init\Binding\Loader;

use WPDesk\Init\Binding\Definition;
use WPDesk\Init\Binding\DefinitionFactory;
use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\Plugin\Plugin;

class ArrayDefinitions implements BindingDefinitions {

	/** @var array */
	private $bindings;

	/** @var DefinitionFactory */
	private $factory;

	public function __construct( array $bindings, ?DefinitionFactory $factory = null) {
		$this->bindings = $bindings;
		$this->factory  = $factory ?? new DefinitionFactory();
	}

	public function load(): iterable {
		yield from $this->normalize( $this->bindings );
	}

	private function normalize( iterable $bindings ): iterable {
		foreach ( $bindings as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $unit ) {
					yield $this->create( $unit, $key );
				}
			} else {
				yield $this->create( $value, $key );
			}
		}
	}

	/**
     * @param mixed $value
	 * @param int|string $hook
	 */
	private function create( $value, $hook ): Definition {
		return $this->factory->create( $value, is_int( $hook ) ? null : $hook );
	}
}
