<?php
/**
 * Plugin Name: Example plugin
 * Version: 1.0.0
 * Text Domain: example-plugin
 * Requires PHP: 7.0
 * Requires at least: 5.0
 */

$plugin = ( new \WPDesk\Init\PluginInit( 'config.php' ) )->init();