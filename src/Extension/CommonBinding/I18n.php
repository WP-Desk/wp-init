<?php

namespace WPDesk\Init\Extension\CommonBinding;

use WPDesk\Init\Binding\Hookable;
use WPDesk\Init\Plugin\Plugin;

class I18n implements Hookable {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function hooks(): void {
		add_action( 'init', $this );
	}

	public function __invoke(): void {
		\load_plugin_textdomain(
			$this->plugin->get_slug(),
			false,
			$this->plugin->header()->get( 'DomainPath' )
		);
	}
}
