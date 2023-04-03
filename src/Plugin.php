<?php
declare( strict_types=1 );

namespace WPDesk\Init;

use WPDesk\Init\HookProvider\Deferred;
use WPDesk\Init\HookProvider\HooksProvider;
use WPDesk\Init\HookProvider\Conditional;

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

	/**
	 * Current plugin execution environment.
	 *
	 * @var string
	 */
	private $environment;

	public function __construct(
		string $file,
		string $name,
		string $version,
		?string $slug = null,
		string $environment = 'prod'
	) {
		$this->file        = $file;
		$this->name        = $name;
		$this->version     = $version;
		$this->environment = $environment;
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
		return $this->name ?? $this->get_slug();
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
	public function get_url( string $path = '' ) {
		return $this->url . ltrim( $path, '/' );
	}

	public function get_environment(): string {
		return $this->environment;
	}

	public function is_environment( string $env ): bool {
		return $this->environment === $env;
	}

	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Register hooks for the plugin.
	 */
	public function register_hooks( HooksProvider ...$providers ): void {
		foreach ( $providers as $provider ) {
			if ( $provider instanceof Conditional && ! $provider->is_needed() ) {
				continue;
			}

			if ( $provider instanceof Deferred ) {
				$register_after    = array_values( (array) $provider->register_after() );
				$register_after[1] = $register_after[1] ?? 10;
				[ $hook, $priority ] = $register_after;
				add_action( $hook, static function () use ( $provider ) {
					$provider->register_hooks();
				}, $priority, 0 );

			} else {
				$provider->register_hooks();
			}
		}
	}

}
