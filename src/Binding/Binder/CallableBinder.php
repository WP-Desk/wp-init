<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Binder;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\ComposableBinder;
use WPDesk\Init\Binding\Definition;
use WPDesk\Init\Binding\Definition\CallableDefinition;
use WPDesk\Init\Binding\Exception\InvalidCallableBinding;

/**
 * @internal Binding implementation detail.
 */
final class CallableBinder implements ComposableBinder {

	private ContainerInterface $container;

	public function __construct( ContainerInterface $c ) {
		$this->container = $c;
	}

	public function can_bind( Definition $def ): bool {
		return $def instanceof CallableDefinition;
	}

	public function bind( Definition $def ): void {
		$callable   = $this->normalize_callable( $def->value() );
		$ref        = new \ReflectionFunction( $callable );
		$parameters = [];

		foreach ( $ref->getParameters() as $ref_param ) {
			$parameters[] = $this->resolve_parameter( $ref_param );
		}

		$ref->invokeArgs( $parameters );
	}

	private function normalize_callable( callable $callable ): \Closure {
		return \Closure::fromCallable( $callable );
	}

	private function resolve_parameter( \ReflectionParameter $parameter ) {
		$type = $parameter->getType();
		if ( ! $type instanceof \ReflectionNamedType ) {
			throw new InvalidCallableBinding(
				sprintf(
					'Callable binding parameter "$%s" must have a single named class/interface type.',
					$parameter->getName()
				)
			);
		}

		if ( $type->isBuiltin() ) {
			throw new InvalidCallableBinding(
				sprintf(
					'Callable binding parameter "$%s" cannot use builtin type "%s".',
					$parameter->getName(),
					$type->getName()
				)
			);
		}

		$dependency = $type->getName();
		if ( ! $this->container->has( $dependency ) ) {
			throw new InvalidCallableBinding(
				sprintf(
					'Callable binding parameter "$%s" requires container entry "%s", which is not available.',
					$parameter->getName(),
					$dependency
				)
			);
		}

		return $this->container->get( $dependency );
	}
}
