<?php

declare( strict_types=1 );

namespace WPDesk\Init\HookProvider;

use WPDesk\Init\ContainerAwareInterface;
use WPDesk\Init\ContainerAwareTrait;
use WPDesk\Init\HooksProvider;
use WPDesk\Init\PluginAwareInterface;
use WPDesk\Init\PluginAwareTrait;

class ContainerHookProvider implements HooksProvider, PluginAwareInterface, ContainerAwareInterface {
	use PluginAwareTrait;
	use ContainerAwareTrait;

	public function register_hooks(): void {
		add_action(
			'plugins_loaded',
			function () {
				if ( $this->container->has( 'hooks' ) ) {
					foreach ( $this->container->get( 'hooks' ) as $hook_provider ) {
						$this->plugin->register_hooks( $hook_provider );
					}
				}
			}
		);
	}

}
