<?php

namespace WPDesk\Init\HookDriver\Legacy;

use Psr\Container\ContainerInterface;
use Traversable;
use WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * @implements IteratorAggregate<int,Hookable>
 */
final class HooksRegistry implements \IteratorAggregate {

	private static $instance;

	/** @var array<class-string<Hookable>|Hookable> */
	private $callbacks = [];

	private $container;

	private function __construct() {}

	public function inject_container( ContainerInterface $c ) {
		$this->container = $c;
	}

	public static function instance(): HooksRegistry {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getIterator(): Traversable {
		return new \ArrayIterator(
			array_map(
				function ( $hookable ) {
					if ( is_string( $hookable ) ) {
						return $this->container->get( $hookable );
					}

					return $hookable;
				},
				$this->callbacks
			)
		);
	}

	public function add( $hookable ) {
		$this->callbacks[] = $hookable;
	}
}
