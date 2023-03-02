<?php

declare( strict_types=1 );

namespace WPDesk\Init\HookProvider;

use WPDesk\Init\HooksProvider;
use WPDesk\Init\PluginAwareInterface;
use WPDesk\Init\PluginAwareTrait;

class ActivationDate implements HooksProvider, PluginAwareInterface {
	use PluginAwareTrait;

	public function register_hooks(): void {
		add_action(
			'activated_plugin',
			function ( $plugin_file, $network_wide = false ) {
				if ( ! $network_wide && $this->plugin->get_basename() === $plugin_file ) {
					$option_name     = 'plugin_activation_' . $plugin_file;
					$activation_date = get_option( $option_name, '' );
					if ( '' === $activation_date ) {
						$activation_date = current_time( 'mysql' );
						update_option( $option_name, $activation_date );
					}
				}
			}
		);

	}

}
