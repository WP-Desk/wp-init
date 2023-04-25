<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookDriver;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Configuration\ReadableConfig;

/**
 * Hook can be attached to WordPress in different ways, and this interface decouples possible methods
 * from our initialization system, to make it more flexible.
 *
 * Even though hook registration is sort of the main purpose of this library, it's better to encapsulate
 * hook registration in a separate class, so that it can be easily replaced with a different
 * implementation and composition.
 */
interface HookDriver {

	/**
	 * @param ReadableConfig $config
	 * @param array $bundles
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	public function register_hooks( ReadableConfig $config, array $bundles, ContainerInterface $container ): void;

}