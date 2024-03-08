<?php
/**
 * This file have to be compatible with PHP >=7.0 to gracefully handle outdated client's websites.
 */

namespace WPDesk\Init;

use WPDesk\Init\Binding\Binder\CallableBinder;
use WPDesk\Init\Binding\Binder\CompositeBinder;
use WPDesk\Init\Binding\Binder\HookableBinder;
use WPDesk\Init\Binding\Loader\DirectoryBasedLoader;
use WPDesk\Init\Binding\DefinitionFactory;
use WPDesk\Init\CommonBinding\I18n;
use WPDesk\Init\Binding\Loader\ArrayBindingLoader;
use WPDesk\Init\Binding\Loader\CompositeBindingLoader;
use WPDesk\Init\Extension\LegacyExtension;
use WPDesk\Init\Extension\BuiltinExtension;
use WPDesk\Init\Extension\ConfigExtension;
use WPDesk\Init\Extension\ExtensionsSet;
use WPDesk\Init\HookDriver\CompositeDriver;
use WPDesk\Init\HookDriver\GenericDriver;
use WPDesk\Init\HookDriver\HookDriver;
use WPDesk\Init\HookDriver\LegacyHookableDriver;
use WPDesk\Init\Loader\PhpFileLoader;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\Util\Path;

final class Init {

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

	public function boot() {
		if ( self::$bootable === false ) {
			return;
		}

		$backtrace = \debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS, 1 );
		$filename  = $backtrace[0]['file'];

		$extensions = new ExtensionsSet();
		$this->discover_extensions( $extensions );

		$kernel = new Kernel( $filename, $this->config, $extensions );
		$kernel->boot();
	}

	private function discover_extensions( $extensions ) {
		$extensions->add( new ConfigExtension() );
		$extensions->add( new BuiltinExtension() );

		if ( class_exists( \WPDesk_Plugin_Info::class ) ) {
			$extensions->add( new LegacyExtension() );
		}
	}
}
