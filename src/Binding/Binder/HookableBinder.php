<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Binder;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\ComposableBinder;
use WPDesk\Init\Binding\Definition;
use WPDesk\Init\Binding\Definition\HookableDefinition;
use WPDesk\Init\Binding\Exception\InvalidBindingDefinition;

/**
 * @internal Binding implementation detail.
 */
class HookableBinder implements ComposableBinder {

	private ContainerInterface $container;

	public function __construct( ContainerInterface $c ) {
		$this->container = $c;
	}

	public function can_bind( Definition $def ): bool {
		return $def instanceof HookableDefinition;
	}

	/** @param Definition<mixed> $def */
	public function bind( Definition $def ): void {
		if ( ! $def instanceof HookableDefinition ) {
			throw new InvalidBindingDefinition( sprintf( 'Expected %s binding definition.', HookableDefinition::class ) );
		}

		$this->container->get( $def->value() )->hooks();
	}
}
