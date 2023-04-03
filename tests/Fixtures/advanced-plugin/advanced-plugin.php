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

$plugin = ( new \WPDesk\Init\PluginInit( [
	'bundles'               => [
		\WPDesk\Init\Bundle\ContainerBundle::class
	],
	'cache_path'            => 'generated',
	'require'               => [],
	'container_definitions' => [],
	'hook_subscribers'      => [
		\WPDesk\Init\Bundle\ContainerBundle::class
	],
] ) )->init();
