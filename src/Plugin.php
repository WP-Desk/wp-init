<?php
declare( strict_types=1 );

namespace WPDesk\Init;

final class Plugin {

	/**
	 * Plugin basename.
	 *
	 * Ex: plugin-name/plugin-name.php
	 *
	 * @var string
	 */
	private $basename;

	/**
	 * Absolute path to the main plugin directory.
	 *
	 * @var string
	 */
	private $directory;

	/**
	 * Plugin name to display.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Absolute path to the main plugin file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Plugin identifier.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * URL to the main plugin directory.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Plugin version string.
	 *
	 * @var string
	 */
	private $version;

	public function __construct(
		string $file,
		string $name,
		string $version,
		?string $slug = null,
	) {
		$this->file        = $file;
		$this->name        = $name;
		$this->version     = $version;
		$this->basename    = plugin_basename( $file );
		$this->directory   = rtrim( plugin_dir_path( $file ), '/' ) . '/';
		$this->url         = rtrim( plugin_dir_url( $file ), '/' ) . '/';
		$this->slug        = $slug ?? basename( $this->directory );
	}

	/**
	 * Retrieve the absolute path for the main plugin file.
	 */
	public function get_basename(): string {
		return $this->basename;
	}

	/**
	 * Retrieve the plugin directory.
	 */
	public function get_directory(): string {
		return $this->directory;
	}

	/**
	 * Retrieve the path to a file in the plugin.
	 *
	 * @param string $path Optional. Path relative to the plugin root.
	 */
	public function get_path( string $path = '' ): string {
		return $this->directory . ltrim( $path, '/' );
	}

	/**
	 * Retrieve the absolute path for the main plugin file.
	 */
	public function get_file(): string {
		return $this->file;
	}

	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Retrieve the plugin identifier.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Retrieve the URL for a file in the plugin.
	 *
	 * @param string $path Optional. Path relative to the plugin root.
	 */
	public function get_url( string $path = '' ): string {
		return $this->url . ltrim( $path, '/' );
	}

	public function get_version(): string {
		return $this->version;
	}

}
