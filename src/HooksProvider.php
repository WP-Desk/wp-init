<?php
declare( strict_types=1 );

namespace WPDesk\Init;

/**
 * Hook is a special kind of service which lives only to integrate with WordPress lifecycle
 * system. By design, hook providers should be lightweight classes which focus its main logic on
 * integration with actions and filters.
 *
 * @author Brady Vercher
 */
interface HooksProvider {

	/**
	 * Register hooks for the plugin.
	 */
	public function register_hooks(): void;

}