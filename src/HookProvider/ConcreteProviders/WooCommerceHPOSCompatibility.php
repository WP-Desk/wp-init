<?php

declare( strict_types=1 );

namespace WPDesk\Init\HookProvider\ConcreteProviders;

use WPDesk\Init\HookProvider\HooksProvider;
use WPDesk\Init\Plugin;
use WPDesk\WPHook\HookSubscriber\Conditional;
use WPDesk\WPHook\HookSubscriber\Deferred;
use WPDesk\WPHook\HookSubscriber\HookSubscriber;

class WooCommerceHPOSCompatibility implements HookSubscriber, Deferred, Conditional {

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public static function register(): iterable {
		yield 'before_woocommerce_init' => 'register_hooks';
	}

	public static function register_after() {
		return 'woocommerce_init';
	}

	public function is_needed(): bool {
		return class_exists( 'WooCommerce' );
	}

	public function register_hooks(): void {
		$features_util_class = '\\' . 'Automattic' . '\\' . 'WooCommerce' . '\\' . 'Utilities' . '\\' . 'FeaturesUtil';
		if ( class_exists( $features_util_class ) ) {
			$features_util_class::declare_compatibility( 'custom_order_tables', $this->plugin->get_basename(), true );
		}
	}
}
