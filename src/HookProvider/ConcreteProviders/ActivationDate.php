<?php

declare( strict_types=1 );

namespace WPDesk\Init\HookProvider\ConcreteProviders;

use WPDesk\Init\Plugin;
use WPDesk\WPHook\HookSubscriber\HookSubscriber;

class ActivationDate implements HookSubscriber {

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public static function register(): iterable {
		yield 'activated_plugin' => 'save_activation_date';
	}

	public function save_activation_date( $plugin_file, $network_wide = false ): void {
		if ( ! $network_wide && $this->plugin->get_basename() === $plugin_file ) {
			$option_name     = 'plugin_activation_' . $plugin_file;
			$activation_date = get_option( $option_name, '' );
			if ( '' === $activation_date ) {
				$activation_date = current_time( 'mysql' );
				update_option( $option_name, $activation_date );
			}
		}
	}
}
