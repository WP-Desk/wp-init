<?php
declare( strict_types=1 );

namespace WPDesk\Init;

use DI\Container;
use DI\ContainerBuilder as DiBuilder;
use WPDesk\Init\Bundle\Bundle;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Dumper\PhpFileDumper;
use WPDesk\Init\HookDriver\HookDriver;
use WPDesk\Init\HookDriver\CallbackDriver;
use WPDesk\Init\Loader\PhpFileLoader;

/**
 * Plugin builder class responsible for our initialization system.
 *
 * @api
 */
final class PluginInit {
	/** @var Bundle[] */
	private $bundles = [];

	/** @var string|null Plugin filename. */
	private $filename;

	/** @var Configuration */
	private $config;

	/** @var PhpFileLoader */
	private $loader;

	/** @var HookDriver */
	private $driver;

	/** @var HeaderParser */
	private $parser;

	/**
	 * @param string|array|Configuration $config
	 */
	public function __construct(
		$config,
		?HookDriver $driver = null,
		?HeaderParser $parser = null
	) {
		$this->loader = new PhpFileLoader();
		if ( $config instanceof Configuration ) {
			$this->config = $config;
		} elseif ( \is_array( $config ) ) {
			$this->config = new Configuration( $config );
		} elseif ( \is_string( $config ) ) {
			$this->config = new Configuration( $this->loader->load( $config ) );
		} else {
			throw new \InvalidArgumentException( sprintf( 'Configuration must be either path to configuration file, array of configuration data or %s instance', Configuration::class ) );
		}

		$this->driver = $driver ?? new CallbackDriver();
		$this->parser = $parser ?? new DefaultHeaderParser();
	}

	/**
	 * Build and return a plugin.
	 *
	 * @return Plugin|null If plugin failed to build (e.g. requirements are not fulfilled),
	 * initialization process returns null. There are no exceptions thrown on foreseeable issues
	 * as those cases should be handled gracefully, by displaying admin notice if possible and
	 * preventing to initialize plugin functions without disrupting a website.
	 */
	public function init(): ?Plugin {
		if ( empty( $this->filename ) ) {
			// TODO: We have to fina a better way, as you can either call it directly or use Init::from_config().
			$backtrace      = \debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS, 1 );
			$this->filename = $backtrace[0]['file'];
		}

		$cache_path = $this->get_cache_dir() . '/plugin.php';
		try {
			$plugin_data = $this->loader->load( $cache_path );
		} catch ( \Exception $e ) {
			$dumper = new PhpFileDumper();
			$dumper->dump( $this->parser->parse( $this->filename ), $cache_path );

			$plugin_data = $this->loader->load( $cache_path );
		}

		$plugin = $this->create_plugin( $plugin_data );

		$container = $this->initialize_container( $plugin );
		$container->set( Plugin::class, $plugin );

		$this->driver->register_hooks( $this->config, $this->bundles, $container );

		return $plugin;
	}

	private function get_cache_dir(): string {
		return $this->filename . '/' . $this->config->get( 'cache_path', 'generated' );
	}

	private function initialize_container( Plugin $plugin ): Container {
		$original_builder = new DiBuilder();
		$builder = new ContainerBuilder( $original_builder );
		$builder->add_definitions( $this->config->get( 'container_definitions', [] ) );
		$builder->add_definitions( __DIR__ . '/Resources/services.inc.php' );

		return $builder->build();
	}

  /**
   * @param array{Name: string, Version?: string, TextDomain: string} $plugin_data
   */
	private function create_plugin( array $plugin_data ): Plugin {
		return new Plugin(
			$this->filename,
			$plugin_data['Name'],
			$plugin_data['Version'] ?? '0.0.0',
			$plugin_data['TextDomain'],
		);
	}

}
