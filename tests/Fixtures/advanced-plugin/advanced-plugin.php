<?php

/**
 * Plugin Name: ShopMagic for WooCommerce
 * Plugin URI: https://shopmagic.app/
 * Description: Marketing Automation and Custom Email Designer for WooCommerce
 * Version: 3.0.9-beta.1
 * Author: WP Desk
 * Author URI: https://shopmagic.app/
 * Text Domain: shopmagic-for-woocommerce
 * Domain Path: /lang/
 * Requires at least: 5.0
 * Tested up to: 6.1
 * WC requires at least: 4.8
 * WC tested up to: 7.2
 * Requires PHP: 7.2
 */

require __DIR__ . '/../../../vendor/autoload.php';

\WPDesk\Init\Init::setup(
	[
		'cache_path' => 'generated',
	]
)->boot( __FILE__ );
