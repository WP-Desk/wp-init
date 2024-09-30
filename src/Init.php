<?php
/**
 * This file have to be compatible with PHP >=7.0 to gracefully handle outdated client's websites.
 */

namespace WPDesk\Init;

use WPDesk\Init\Extension\LegacyExtension;
use WPDesk\Init\Extension\BuiltinExtension;
use WPDesk\Init\Extension\ConfigExtension;
use WPDesk\Init\Extension\ExtensionsSet;
use WPDesk\Init\Util\PhpFileLoader;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\Extension\ConditionalExtension;

final class Init {

	/** @var bool */
	private static $bootable = true;

	/** @var Configuration */
	private $config;

	/**
	 * @param string|array|Configuration $config
	 */
	public static function setup( $config ) {
		$result = require __DIR__ . '/platform_check.php';

		if ( $result === false ) {
			self::$bootable = false;
		}

		return new self( $config );
	}

	/**
	 * @param string|array|Configuration $config
	 */
	public function __construct( $config ) {
		if ( $config instanceof Configuration ) {
			$this->config = $config;
		} elseif ( \is_array( $config ) ) {
			$this->config = new Configuration( $config );
		} elseif ( \is_string( $config ) ) {
			$loader       = new PhpFileLoader();
			$this->config = new Configuration( $loader->load( $config ) );
		} else {
			throw new \InvalidArgumentException( sprintf( 'Configuration must be either path to configuration file, array of configuration data or %s instance', Configuration::class ) );
		}
	}

	/**
	 * @param string|null $filename Filename of the booted plugin. May be null, if called from plugin's main file.
	 */
	public function boot( ?string $filename = null ) {
		if ( self::$bootable === false ) {
			return;
		}

		if ( $filename === null ) {
			$backtrace = \debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS, 1 ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
			$filename  = $backtrace[0]['file'];
		}

		$extensions = new ExtensionsSet(
			new BuiltinExtension(),
			new ConfigExtension(),
			new ConditionalExtension()
		);

		if ( $this->config->get( 'legacy', false ) && class_exists( \WPDesk_Plugin_Info::class ) ) {
			$extensions->add( new LegacyExtension() );
		}

		$kernel = new Kernel( $filename, $this->config, $extensions );

		$kernel->boot();
	}
}
