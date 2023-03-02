<?php
declare( strict_types=1 );

namespace WPDesk\Init;

/**
 * @author Brady Vercher
 */
trait PluginAwareTrait {
	/** @var Plugin */
	protected $plugin;

	/**
	 * Set the main plugin instance.
	 */
	public function set_plugin( Plugin $plugin ): void {
		$this->plugin = $plugin;
	}
}