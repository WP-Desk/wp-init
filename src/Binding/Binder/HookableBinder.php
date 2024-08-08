<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Binder;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\ComposableBinder;
use WPDesk\Init\Binding\Definition;
use WPDesk\Init\Binding\Definition\HookableDefinition;

class HookableBinder implements ComposableBinder {

	/** @var ContainerInterface */
	private $container;

	public function __construct( ContainerInterface $c ) {
		$this->container = $c;
	}

	public function can_bind( Definition $def ): bool {
		return $def instanceof HookableDefinition;
	}

	public function bind( Definition $def ): void {
		$this->container->get( $def->value() )->hooks();
	}
}
