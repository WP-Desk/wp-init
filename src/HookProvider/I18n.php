<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookProvider;

use WPDesk\Init\HooksProvider;
use WPDesk\Init\PluginAwareInterface;
use WPDesk\Init\PluginAwareTrait;

class I18n implements HooksProvider, PluginAwareInterface {
	use PluginAwareTrait;

	public function register_hooks(): void {
		if ( did_action( 'plugins_loaded' ) ) {
			$this->load_textdomain();
		} else {
			add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		}
	}

	public function load_textdomain(): void {
		$plugin_rel_path = dirname( $this->plugin->get_basename() ) . '/lang';
		\load_plugin_textdomain( $this->plugin->get_slug(), false, $plugin_rel_path );
	}
}
