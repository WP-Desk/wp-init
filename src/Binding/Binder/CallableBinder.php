<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Binder;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\ComposableBinder;
use WPDesk\Init\Binding\Definition;
use WPDesk\Init\Binding\Definition\CallableDefinition;

class CallableBinder implements ComposableBinder {

	/** @var ContainerInterface */
	private $container;

	public function __construct( ContainerInterface $c ) {
		$this->container = $c;
	}

	public function can_bind( Definition $def ): bool {
		return $def instanceof CallableDefinition;
	}

	public function bind( Definition $def ): void {
		$ref        = new \ReflectionFunction( $def->value() );
		$parameters = [];
		foreach ( $ref->getParameters() as $ref_param ) {
			$parameters[] = $this->container->get( $ref_param->getType()->getName() );
		}
		$ref->invokeArgs( $parameters );
	}
}
