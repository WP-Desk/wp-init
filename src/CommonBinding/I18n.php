<?php

namespace WPDesk\Init\CommonBinding;

use WPDesk\Init\Binding\Hookable;
use WPDesk\Init\Plugin\Plugin;

class I18n implements Hookable {

	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function hooks(): void {
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
