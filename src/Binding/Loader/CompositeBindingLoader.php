<?php
declare( strict_types=1 );

namespace WPDesk\Init\Binding\Loader;

use WPDesk\Init\Plugin\Plugin;

class CompositeBindingLoader implements BindingDefinitions {

	/** @var BindingDefinitionLoader[] */
	private $loaders;

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
