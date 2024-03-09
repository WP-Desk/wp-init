<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding;

use WPDesk\Init\Binding\Definition\CallableDefinition;
use WPDesk\Init\Binding\Definition\HookBinderDefinition;
use WPDesk\Init\Binding\Definition\HookableDefinition;
use WPDesk\Init\Binding\Definition\UnknownDefinition;
use WPDesk\PluginBuilder\Plugin\Hookable;

class DefinitionFactory {

	public function create( $value, ?string $hook ): Definition {
		if ( is_string( $value ) && class_exists( $value ) ) {
			if ( is_subclass_of( $value, Hookable::class, true ) ) {
				return new HookableDefinition( $value, $hook );
			}

			if ( is_subclass_of( $value, HookBinder::class, true ) ) {
				return new HookBinderDefinition( $value, $hook );
			}
		}

		if ( is_callable( $value ) ) {
			return new CallableDefinition( $value, $hook );
		}

		return new UnknownDefinition( $value, $hook );
	}
}
