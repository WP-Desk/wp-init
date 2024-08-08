<?php

namespace WPDesk\Init\Extension\CommonBinding;

use WPDesk\Init\Binding\Hookable;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\License\PluginRegistrator;

class WPDeskLicenseBridge implements Hookable {

	/** @var \WPDesk_Plugin_Info */
	private $plugin_info;

	private $registrator;

	public function __construct( \WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
	}

	public function hooks(): void {
		$this->registrator = $this->register_plugin();
		// add_action('plugins_loaded', $this);
	}

	public function __invoke(): void {
		$is_plugin_subscription_active = $this->registrator instanceof PluginRegistrator && $this->registrator->is_active();
		if ( $this->plugin instanceof ActivationAware && $is_plugin_subscription_active ) {
			$this->plugin->set_active();
		}

	}

	private function register_plugin() {
		if ( apply_filters( 'wpdesk_can_register_plugin', true, $this->plugin_info ) ) {
			$registrator = new PluginRegistrator( $this->plugin_info );
			$registrator->initialize_license_manager();

			return $registrator;
		}
	}

}
