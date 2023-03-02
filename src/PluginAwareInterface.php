<?php
declare( strict_types=1 );

namespace WPDesk\Init;

/**
 * Plugin aware interface.
 *
 * @author Brady Vercher
 */
interface PluginAwareInterface {
	/**
	 * Set the main plugin instance.
	 */
	public function set_plugin( Plugin $plugin ): void;
}