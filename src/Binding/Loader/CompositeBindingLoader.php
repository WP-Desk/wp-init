<?php
declare( strict_types=1 );

namespace WPDesk\Init\Binding\Loader;

/**
 * @internal Binding loader implementation detail.
 */
class CompositeBindingLoader implements BindingDefinitions {

	/** @var BindingDefinitions[] */
	private array $loaders;

	public function __construct( BindingDefinitions ...$loaders ) {
		$this->loaders = $loaders;
	}

	public function load(): iterable {
		foreach ( $this->loaders as $loader ) {
			yield from $loader->load();
		}
	}

	public function add( BindingDefinitions $loader ): void {
		$this->loaders[] = $loader;
	}
}
