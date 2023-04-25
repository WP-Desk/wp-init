<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookDriver;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Configuration\ReadableConfig;

class HookableDriver implements HookDriver {

	public function register_hooks( ReadableConfig $config, array $bundles, ContainerInterface $container ): void {
		$subscribers = $config->get( 'hookables', [] );

		foreach ( $bundles as $bundle ) {
			$subscribers = \array_merge( $subscribers, $bundle::hookable() );
		}

		foreach ( $subscribers as $subscriber ) {
			$container->get( $subscriber )->hooks();
		}
	}

}