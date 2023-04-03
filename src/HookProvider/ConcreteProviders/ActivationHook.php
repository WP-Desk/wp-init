<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookProvider\ConcreteProviders;

use WPDesk\Init\HookProvider\HooksProvider;
use WPDesk\Init\Plugin;

abstract class ActivationHook implements HooksProvider {
	/** @var Plugin */
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function register_hooks(): void {
		register_activation_hook(
			$this->plugin->get_basename(),
			[$this, 'activate']
		);
	}

	abstract public function activate(): void;
}