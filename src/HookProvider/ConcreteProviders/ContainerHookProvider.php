<?php

declare( strict_types=1 );

namespace WPDesk\Init\HookProvider\ConcreteProviders;

use Psr\Container\ContainerInterface;
use WPDesk\Init\HookProvider\HooksProvider;
use WPDesk\Init\Plugin;

class ContainerHookProvider implements HooksProvider {

	/** @var Plugin */
	private $plugin;

	/** @var ContainerInterface */
	private $container;

	public function __construct( Plugin $plugin, ContainerInterface $container ) {
		$this->plugin    = $plugin;
		$this->container = $container;
	}

	public function register_hooks(): void {
		add_action(
			'plugins_loaded',
			function () {
				if ( $this->container->has( 'hooks' ) ) {
					$this->plugin->register_hooks( ...$this->container->get( 'hooks' ) );
				}
			}
		);
	}

}
