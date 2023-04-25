<?php
/**
 * This file have to be compatible with PHP >=5.6 to gracefully handle outdated client's websites.
 */

namespace WPDesk\Init;

class InitCompat {

	public static function from_config( $config_path, $environment = null ) {
		require __DIR__ . '/platform_check.php';

		$init = new PluginInit( $config_path, $environment );

		return $init->init();
	}

}