<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding;

use WPDesk\Init\Binding\Definition\CallableDefinition;
use WPDesk\Init\Binding\Definition\HookableDefinition;
use WPDesk\Init\Binding\Exception\InvalidBindingDefinition;

class DefinitionFactory {

	/**
	 * @param mixed $value
	 * @param array<string, mixed> $options
	 *
	 * @return Definition<mixed>
	 */
	public function create( $value, ?string $hook, array $options = [] ): Definition {
		if ( is_string( $value ) && class_exists( $value ) && is_subclass_of( $value, Hookable::class, true ) ) {
			return new HookableDefinition( $value, $hook, $options );
		}

		if ( is_callable( $value ) ) {
			return new CallableDefinition( $value, $hook, $options );
		}

		throw new InvalidBindingDefinition(
			sprintf(
				'Invalid binding for hook "%s". Expected a hookable class-string or callable, got %s.',
				$hook ?? '<bootstrap>',
				is_object( $value ) ? get_class( $value ) : gettype( $value )
			)
		);
	}
}
