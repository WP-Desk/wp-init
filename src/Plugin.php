<?php
declare( strict_types=1 );

namespace WPDesk\Init;

final class Plugin implements ContainerAwareInterface {
	use ContainerAwareTrait;

	/**
	 * Plugin basename.
	 *
	 * Ex: plugin-name/plugin-name.php
	 *
	 * @var string
	 */
	protected $basename;

	/**
	 * Absolute path to the main plugin directory.
	 *
	 * @var string
	 */
	protected $directory;

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
	protected $file;

	/**
	 * Plugin identifier.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * URL to the main plugin directory.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Retrieve the absolute path for the main plugin file.
	 *
	 * @return string
	 */
	public function get_basename(): string {
		return $this->basename;
	}

	/**
	 * Set the plugin basename.
	 *
	 * @param string $basename Relative path from the main plugin directory.
	 *
	 * @return $this
	 */
	public function set_basename( string $basename ): self {
		$this->basename = $basename;

		return $this;
	}

	/**
	 * Retrieve the plugin directory.
	 *
	 * @return string
	 */
	public function get_directory(): string {
		return $this->directory;
	}

	/**
	 * Set the plugin's directory.
	 *
	 * @param string $directory Absolute path to the main plugin directory.
	 *
	 * @return $this
	 */
	public function set_directory( string $directory ): self {
		$this->directory = rtrim( $directory, '/' ) . '/';

		return $this;
	}

	/**
	 * Retrieve the path to a file in the plugin.
	 *
	 * @param string $path Optional. Path relative to the plugin root.
	 *
	 * @return string
	 */
	public function get_path( string $path = '' ): string {
		return $this->directory . ltrim( $path, '/' );
	}

	/**
	 * Retrieve the absolute path for the main plugin file.
	 *
	 * @return string
	 */
	public function get_file(): string {
		return $this->file;
	}

	/**
	 * Set the path to the main plugin file.
	 *
	 * @param string $file Absolute path to the main plugin file.
	 *
	 * @return $this
	 */
	public function set_file( string $file ): self {
		$this->file = $file;

		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_name(): string {
		return $this->name ?? $this->get_slug();
	}

	/**
	 * Retrieve the plugin identifier.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Set the plugin identifier.
	 *
	 * @param string $slug Plugin identifier.
	 *
	 * @return $this
	 */
	public function set_slug( string $slug ) {
		$this->slug = $slug;

		return $this;
	}

	/**
	 * Retrieve the URL for a file in the plugin.
	 *
	 * @param string $path Optional. Path relative to the plugin root.
	 *
	 * @return string
	 */
	public function get_url( string $path = '' ) {
		return $this->url . ltrim( $path, '/' );
	}

	/**
	 * Set the URL for plugin directory root.
	 *
	 * @param string $url URL to the root of the plugin directory.
	 *
	 * @return $this
	 */
	public function set_url( string $url ) {
		$this->url = rtrim( $url, '/' ) . '/';

		return $this;
	}

	/**
	 * Register hooks for the plugin.
	 *
	 * @param HooksProvider ...$providers
	 *
	 * @return void
	 */
	public function register_hooks( HooksProvider ...$providers ): void {
		foreach ( $providers as $provider ) {
			if ( $provider instanceof PluginAwareInterface ) {
				$provider->set_plugin( $this );
			}

			if ( $provider instanceof ContainerAwareInterface ) {
				$provider->set_container( $this->container );
			}

			if ( $provider instanceof Conditional && ! $provider->is_needed() ) {
				continue;
			}

			$provider->register_hooks();
		}
	}

}
