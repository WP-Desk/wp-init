<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookDriver;

use Psr\Container\ContainerInterface;
use WPDesk\Init\HookDriver\Legacy\HooksRegistry;

class LegacyHookableDriver implements HookDriver {

	/** @var ContainerInterface */
	private $container;

	public function __construct( ContainerInterface $container ) {
		if ( ! class_exists( \WPDesk_Plugin_Info::class ) ) {
			throw new \LogicException( 'Legacy driver cannot be used as the plugin builder component is unavailable. Try running "composer require wpdesk/wp-builder".' );
		}
		$this->container = $container;
	}

	public function register_hooks(): void {
		HooksRegistry::instance()->inject_container( $this->container );

		$info       = $this->container->get( \WPDesk_Plugin_Info::class );
		$class_name = $info->get_class_name();
		$p          = new $class_name( $info );
		add_action( 'plugins_loaded', [ $p, 'init' ], -45 );
	}
}
