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
 */
final class PluginInit {
	private const ENV_PRODUCTION = 'prod';
	private const ENV_DEVELOPMENT = 'dev';

	/** @var Bundle[] */
	private $bundles = [];

	/** @var string|null Plugin filename. */
	private $filename;

	/** @var Configuration */
	private $config;

	/** @var string */
	private $env;

	/** @var PhpFileLoader */
	private $loader;

	/** @var HookDriver */
	private $driver;

	/** @var HeaderParser */
	private $parser;

	/**
	 * @param string|array|Configuration $config
	 * @param string $environment
	 */
	public function __construct(
		$config,
		string $environment = self::ENV_PRODUCTION,
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
			throw new \InvalidArgumentException( 'Invalid configuration' );
		}

		$this->env    = $environment;
		$this->driver = $driver ?? new CallbackDriver();
		$this->parser = $parser ?? new PluginHeaderParser();
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

		$cache_path = $this->config->get( 'cache_path', 'generated' ) . '/plugin.php';
		try {
			$plugin_data = $this->loader->load( $cache_path );
		} catch ( \Exception $e ) {
			$dumper = new PhpFileDumper();
			$dumper->dump( $this->parser->parse( $this->filename ), $cache_path );

			$plugin_data = $this->loader->load( $cache_path );
		}

		$plugin = $this->create_plugin( $plugin_data );

		$requirements = \array_merge(
			[
				// Prepend requirements from plugin header.
				'wp'  => $plugin_data['RequiresWP'] ?? null,
				'php' => $plugin_data['RequiresPHP'] ?? null,
			],
			$this->config->get( 'require', [] )
		);

		if ( ! $this->check_requirements( $plugin, $requirements ) ) {
			return null;
		}

		foreach ( $this->config->get( 'bundles', [] ) as $bundle ) {
			$this->bundles[ $bundle ] = new $bundle();
		}

		$container = $this->initialize_container( $plugin );

		$container->set( Plugin::class, $plugin );

		$this->driver->register_hooks( $this->config, $this->bundles, $container );

		return $plugin;
	}

	private function get_container_class( Plugin $plugin ): string {
		return \str_replace( '-', '_', $plugin->get_slug() ) . '_container';
	}

	private function initialize_container( Plugin $plugin ): Container {
		$original_builder = new DiBuilder();
		$cache_path       = $plugin->get_path( $this->config->get( 'cache_path', 'generated' ) . '/container' );

		if ( $plugin->is_environment( self::ENV_PRODUCTION ) ) {
			// Skip calling build() on bundles, if we've already compiled the container.
			if ( file_exists( $cache_path . '/' . $this->get_container_class( $plugin ) . '.php' ) ) {
				return $original_builder->build();
			}

			$original_builder->enableCompilation(
				$cache_path,
				$this->get_container_class( $plugin )
			);
		}

		$builder = new ContainerBuilder( $original_builder );
		$builder->add_definitions( $this->config->get( 'container_definitions', [] ) );
		$builder->add_definitions( __DIR__ . '/Resources/services.inc.php' );

		foreach ( $this->bundles as $bundle ) {
			$bundle->build( $builder, $this->config );
		}

		return $builder->build();
	}

	private function create_plugin( array $plugin_data ): Plugin {
		return new Plugin(
			$this->filename,
			$plugin_data['Name'],
			$plugin_data['Version'] ?? '0.0.0',
			$plugin_data['TextDomain'] ?? null,
			$this->env
		);
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @return bool
	 */
	private function check_requirements( Plugin $plugin, array $requirements ): bool {
		$checker_factory = new \WPDesk_Basic_Requirement_Checker_Factory();
		$checker         = $checker_factory->create_from_requirement_array(
			$plugin->get_basename(),
			$plugin->get_name(),
			array_filter( $requirements ),
			$plugin->get_slug()
		);

		if ( ! $checker->are_requirements_met() ) {
			$checker->render_notices();

			return false;
		}

		return true;
	}

}
