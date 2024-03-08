<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Binder;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Binder;
use WPDesk\Init\Binding\Definition;
use WPDesk\Init\Binding\Definition\HookBinderDefinition;
use WPDesk\Init\Binding\Definition\HookableDefinition;

class HookBinderBinder implements Binder {

	/** @var ContainerInterface */
	private $container;

	public function __construct( ContainerInterface $c ) {
		$this->container = $c;
	}

	public function can_bind( Definition $def ): bool {
		return $def instanceof HookBinderDefinition;
	}

	public function bind( Definition $def ): void {
		if ( $this->can_bind( $def ) ) {
			$this->container->get( $def->value() )->bind();
		}
	}
}
