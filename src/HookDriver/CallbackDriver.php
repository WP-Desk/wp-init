<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookDriver;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Configuration\ReadableConfig;

class CallbackDriver implements HookDriver {

	public function register_hooks( ReadableConfig $config, array $bundles, ContainerInterface $container ): void {
		$hooks = $config->get( 'hooks', [] );

		foreach ( $hooks as $hook => $callback_definition ) {
			[ $callback, $priority ] = \array_replace(
				[ null, 10 ],
				(array) $callback_definition
			);

			add_filter( $hook, $callback, $priority );
		}
	}
}