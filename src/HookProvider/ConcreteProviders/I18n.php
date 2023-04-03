<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookProvider\ConcreteProviders;

use WPDesk\Init\Plugin;
use WPDesk\WPHook\HookSubscriber\HookSubscriber;

class I18n implements HookSubscriber {
	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public static function register(): iterable {
		yield 'plugins_loaded' => 'load_textdomain';
	}

	public function load_textdomain(): void {
		$plugin_rel_path = dirname( $this->plugin->get_basename() ) . '/lang';
		\load_plugin_textdomain( $this->plugin->get_slug(), false, $plugin_rel_path );
	}
}
