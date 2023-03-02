<?php

declare( strict_types=1 );

namespace WPDesk\Init\HookProvider;

use WPDesk\Init\HooksProvider;
use WPDesk\Init\PluginAwareInterface;
use WPDesk\Init\PluginAwareTrait;

class WooCommerceHPOSCompatibility implements HooksProvider, PluginAwareInterface {
	use PluginAwareTrait;

	public function register_hooks(): void {
		add_action(
			'before_woocommerce_init',
			function () {
				$features_util_class = '\\' . 'Automattic' . '\\' . 'WooCommerce' . '\\' . 'Utilities' . '\\' . 'FeaturesUtil';
				if ( class_exists( $features_util_class ) ) {
					$features_util_class::declare_compatibility( 'custom_order_tables', $this->plugin->get_basename(), true );
				}
			}
		);
	}
}
