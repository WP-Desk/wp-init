<?php

namespace WPDesk\Init\CommonBinding;

use WPDesk\Init\Binding\HookBinder;
use WPDesk\Init\Plugin\Plugin;

class I18n implements HookBinder {

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function bind(): void {
		if ( did_action( 'plugins_loaded' ) ) {
			$this->load_textdomain();
		} else {
			add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		}
	}

	public function load_textdomain(): void {
		\load_plugin_textdomain(
			$this->plugin->get_slug(),
			false,
			$this->plugin->header()->get( 'DomainPath' )
		);
	}
}
