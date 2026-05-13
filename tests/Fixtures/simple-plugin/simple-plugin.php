<?php
/**
 * Plugin Name: Example plugin
 */

require __DIR__ . '/../../../vendor/autoload.php';

\WPDesk\Init\Init::setup( __DIR__ . '/config.php' )->boot( __FILE__ );
