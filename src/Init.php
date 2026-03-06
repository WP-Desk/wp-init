<?php
/**
 * This file have to be compatible with PHP >=7.0 to gracefully handle outdated client's websites.
 */

namespace WPDesk\Init;

use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\Module\BuiltinModule;
use WPDesk\Init\Module\ConfigModule;
use WPDesk\Init\Module\Module;
use WPDesk\Init\Module\ModuleCollection;
use WPDesk\Init\Util\PhpFileLoader;

final class Init {

	/** @var bool */
	private static $bootable = true;

	/** @var Configuration */
	private $config;

	/**
	 * @param string|array<string,mixed>|Configuration $config
	 *
	 * @return self
	 */
	public static function setup( $config ) {
		$result = require __DIR__ . '/platform_check.php';

		if ( $result === false ) {
			self::$bootable = false;
		}

		return new self( $config );
	}

	/**
	 * @param string|array<string, mixed>|Configuration $config
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
	 *
	 * @return void
	 */
	public function boot( $filename = null ) {
		if ( self::$bootable === false ) {
			return;
		}

		if ( $filename === null ) {
			$backtrace = \debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS, 1 ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
			$filename  = $backtrace[0]['file'];
		}

		$kernel = new Kernel( $filename, $this->config, $this->resolve_modules() );

		$kernel->boot();
	}

	private function resolve_modules(): ModuleCollection {
		$modules = new ModuleCollection(
			new BuiltinModule(),
			new ConfigModule()
		);

		foreach ( array_keys( $this->normalized_module_config() ) as $module_class ) {
			if ( $modules->has( $module_class ) ) {
				continue;
			}

			$module = new $module_class();
			if ( ! $module instanceof Module ) {
				throw new \LogicException( sprintf( 'Configured module "%s" must implement %s.', $module_class, Module::class ) );
			}

			$modules->add( $module );
		}

		return $modules;
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	private function normalized_module_config(): array {
		$modules    = (array) $this->config->get( 'modules', [] );
		$normalized = [];

		foreach ( $modules as $module_class => $module_config ) {
			if ( ! is_string( $module_class ) || $module_class === '' ) {
				throw new \LogicException( 'Configured module keys must be class-string identifiers.' );
			}

			if ( $module_config === null ) {
				$module_config = [];
			}

			if ( ! is_array( $module_config ) ) {
				throw new \LogicException( sprintf( 'Configuration for module "%s" must be an array or null.', $module_class ) );
			}

			$normalized[ $module_class ] = $module_config;
		}

		return $normalized;
	}
}
