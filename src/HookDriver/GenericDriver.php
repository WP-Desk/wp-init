<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookDriver;

use WPDesk\Init\Binding\Binder;
use WPDesk\Init\Binding\Loader\BindingDefinitions;

class GenericDriver implements HookDriver {

	/** @var BindingDefinitionLoader */
	private $definitions;

	/** @var Binder */
	private $binder;

	public function __construct( BindingDefinitions $definitions, Binder $binder ) {
		$this->definitions = $definitions;
		$this->binder      = $binder;
	}

	public function register_hooks(): void {
		foreach ( $this->definitions->load() as $definition ) {
			if ( $definition->hook() ) {
				add_action(
					$definition->hook(),
					function () use ( $definition ) {
						$this->binder->bind( $definition );
					}
				);
			} else {
				$this->binder->bind( $definition );
			}
		}
	}
}
