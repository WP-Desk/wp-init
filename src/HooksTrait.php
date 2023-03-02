<?php
declare( strict_types=1 );

namespace WPDesk\Init;

/**
 * Hooks trait.
 *
 * Allows protected and private methods to be used as hook callbacks in PHP <8.1. Since PHP 8.1
 * you are able to take advantage of first class callable and register private methods in hooks
 * without any workarounds.
 *
 * @author  John P. Bloch
 * @link    https://github.com/johnpbloch/wordpress-dev/blob/master/src/Hooks.php
 */
trait HooksTrait {

	/**
	 * Add a WordPress filter.
	 *
	 * @param callable $method
	 *
	 * @return true
	 */
	protected function add_filter(
		string $hook,
		$method,
		int $priority = 10,
		int $arg_count = 1
	): bool {
		return add_filter(
			$hook,
			$this->map_filter( $method, $arg_count ),
			$priority,
			$arg_count
		);
	}

	/**
	 * Add a WordPress action.
	 *
	 * This is an alias of add_filter().
	 *
	 * @param callable $method
	 *
	 * @return true
	 */
	protected function add_action( string $hook, $method, int $priority = 10, int $arg_count = 1 ): bool {
		return $this->add_filter( $hook, $method, $priority, $arg_count );
	}

	/**
	 * Remove a WordPress filter.
	 *
	 * @param callable $method
	 *
	 * @return bool Whether the function existed before it was removed.
	 */
	protected function remove_filter(
		string $hook,
		$method,
		int $priority = 10,
		int $arg_count = 1
	): bool {
		return remove_filter(
			$hook,
			$this->map_filter( $method, $arg_count ),
			$priority
		);
	}

	/**
	 * Remove a WordPress action.
	 *
	 * This is an alias of remove_filter().
	 *
	 * @param callable $method
	 *
	 * @return bool Whether the function is removed.
	 */
	protected function remove_action(
		string $hook,
		$method,
		int $priority = 10,
		int $arg_count = 1
	): bool {
		return $this->remove_filter( $hook, $method, $priority, $arg_count );
	}

	/**
	 * Map a filter to a closure that inherits the class' internal scope.
	 *
	 * This allows hooks to use protected and private methods.
	 *
	 * @param string $callable
	 * @param int $arg_count
	 *
	 * @return \Closure The callable actually attached to a WP hook
	 */
	private function map_filter( $callable, int $arg_count ): \Closure {
		if ( is_string( $callable ) && method_exists( $this, $callable ) ) {
			$object = $this;
			$method = $callable;
		}

		if ( is_array( $callable ) ) {
			[ $object, $method ] = $callable;
		}

		return static function () use ( $object, $method, $arg_count ) {
			return $object->{$method}( ...array_slice( func_get_args(), 0, $arg_count ) );
		};
	}
}
