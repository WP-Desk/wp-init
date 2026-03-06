<?php
declare( strict_types=1 );

namespace WPDesk\Init;

use DI\Container;
use DI\ContainerBuilder as DiBuilder;
use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Binder\CallableBinder;
use WPDesk\Init\Binding\Binder\CompositeBinder;
use WPDesk\Init\Binding\Binder\HookableBinder;
use WPDesk\Init\Binding\Definition;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Binding\Loader\ClusteredLoader;
use WPDesk\Init\Binding\Loader\CompositeBindingLoader;
use WPDesk\Init\Binding\Loader\OrderedBindingLoader;
use WPDesk\Init\Bootstrap\BootGate;
use WPDesk\Init\Bootstrap\BootstrapContext;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\HookDriver\CompositeDriver;
use WPDesk\Init\HookDriver\GenericDriver;
use WPDesk\Init\HookDriver\HookDriver;
use WPDesk\Init\HookDriver\LegacyDriver;
use WPDesk\Init\Module\LegacyBuilderModule;
use WPDesk\Init\Module\ModuleCollection;
use WPDesk\Init\Plugin\DefaultHeaderParser;
use WPDesk\Init\Plugin\Header;
use WPDesk\Init\Plugin\HeaderParser;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\Init\Util\Path;
use WPDesk\Init\Util\PhpFileDumper;
use WPDesk\Init\Util\PhpFileLoader;

/**
 * @internal Kernel is bootstrap implementation detail. Use Init as the public entrypoint.
 */
final class Kernel {

	/** @var string|null Plugin filename. */
	private ?string $filename;

	private Configuration $config;

	private PhpFileLoader $loader;

	private HeaderParser $parser;

	private ModuleCollection $modules;

	private PhpFileDumper $dumper;

	public function __construct(
		string $filename,
		Configuration $config,
		ModuleCollection $modules
	) {
		$this->filename = $filename;
		$this->config   = $config;
		$this->modules  = $modules;
		$this->loader   = new PhpFileLoader();
		$this->parser   = new DefaultHeaderParser();
		$this->dumper   = new PhpFileDumper();
	}

	public function boot(): void {
		$cache_path = $this->get_cache_path( 'plugin.php' );
		try {
			$plugin_data = $this->loader->load( $cache_path );
		} catch ( \Exception $e ) {
			try {
				$this->dumper->dump(
					$this->parser->parse( $this->filename ),
					$cache_path
				);
				$plugin_data = $this->loader->load( $cache_path );
			} catch ( \Exception $e ) {
				$plugin_data = $this->parser->parse( $this->filename );
			}
		}

		$plugin  = new Plugin( $this->filename, new Header( $plugin_data ) );
		$context = $this->create_context( $plugin );

		$container = $this->initialize_container( $context );
		$container->set( Plugin::class, $plugin );
		$container->set( Configuration::class, $this->config );
		$container->set( BootstrapContext::class, $context );

		$this->register_lifecycle_hooks( $container, $context );

		if ( ! $this->run_gates( $container, $context ) ) {
			return;
		}

		$this->prepare_driver( $container, $context )->register_hooks();
	}

	private function get_cache_path( string $path = '' ): string {
		return (string) ( new Path( $this->config->get( 'cache_path', 'generated' ) ) )->join( $path )->absolute(
			rtrim( plugin_dir_path( $this->filename ), '/' ) . '/'
		);
	}

	/**
	 * Container name in scheme: `<slug>_<version>_container`.
	 *
	 * Container is compiled in client environment, so in order to allow graceful upgrade, include version name to the container. Compiled container class is also autoloaded, so it is necessary that name is unique enough to avoid clash with other plugins.
	 */
	private function get_container_name( Plugin $plugin ): string {
		return preg_replace( '/[^\w_]/', '_', implode( '_', [ $plugin->get_slug(), $plugin->get_version(), 'container' ] ) );
	}

	private function initialize_container( BootstrapContext $context, bool $use_cache = true ): Container {
		$original_builder = new DiBuilder();

		if ( $this->is_prod( $context ) && $use_cache ) {
			$original_builder->enableCompilation(
				$this->get_cache_path(),
				$this->get_container_name( $context->plugin() )
			);
		}

		$builder = new ContainerBuilder( $original_builder );

		if ( ! function_exists( 'WPDesk\Init\DI\create' ) ) {
			require __DIR__ . '/di-functions.php';
		}

		foreach ( $this->modules as $module ) {
			$module->build( $builder, $context );
		}

		try {
			return $builder->build();
		} catch ( \InvalidArgumentException $e ) {
			if ( $use_cache === false ) {
				throw $e;
			}

			return $this->initialize_container( $context, false );
		}
	}

	private function prepare_driver( ContainerInterface $container, BootstrapContext $context ): HookDriver {
		$loader = new CompositeBindingLoader();
		foreach ( $this->modules as $module ) {
			$loader->add( $module->bindings( $container, $context ) );
		}

		$loader = new OrderedBindingLoader(
			new ClusteredLoader( $loader )
		);

		$driver = new GenericDriver(
			$loader,
			new CompositeBinder(
				new HookableBinder( $container ),
				new CallableBinder( $container )
			)
		);

		if ( $this->modules->has( LegacyBuilderModule::class ) ) {
			$driver = new CompositeDriver(
				$driver,
				new LegacyDriver( $container )
			);
		}

		return $driver;
	}

	private function create_context( Plugin $plugin ): BootstrapContext {
		return new BootstrapContext(
			$plugin,
			$this->config,
			$this->normalized_module_config(),
			$this->resolve_environment( $plugin ),
			$this->resolve_debug( $plugin )
		);
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

	private function resolve_environment( Plugin $plugin ): string {
		$environment = $this->config->get( 'environment' );
		if ( is_string( $environment ) && $environment !== '' ) {
			return $environment;
		}

		if ( function_exists( 'wp_get_environment_type' ) ) {
			$wp_environment = wp_get_environment_type();
			if ( is_string( $wp_environment ) && $wp_environment !== '' ) {
				return $wp_environment;
			}
		}

		if ( strpos( $plugin->get_version(), 'dev' ) !== false ) {
			return 'development';
		}

		return 'production';
	}

	private function resolve_debug( Plugin $plugin ): bool {
		if ( $this->config->get( 'debug', false ) ) {
			return true;
		}

		return $this->resolve_environment( $plugin ) === 'development';
	}

	private function is_prod( BootstrapContext $context ): bool {
		return $context->is_development() === false;
	}

	private function run_gates( ContainerInterface $container, BootstrapContext $context ): bool {
		foreach ( $this->boot_gates( $container, $context ) as $gate ) {
			if ( ! $gate->can_boot() ) {
				$gate->on_failure();

				return false;
			}
		}

		return true;
	}

	/**
	 * @return BootGate[]
	 */
	private function boot_gates( ContainerInterface $container, BootstrapContext $context ): array {
		$gates = [];
		foreach ( $this->modules as $module ) {
			foreach ( $module->gates( $container, $context ) as $gate ) {
				$gates[] = $gate;
			}
		}

		return $gates;
	}

	private function register_lifecycle_hooks( ContainerInterface $container, BootstrapContext $context ): void {
		$this->register_activation_hook( $container, $context );
		$this->register_deactivation_hook( $container, $context );
	}

	private function register_activation_hook( ContainerInterface $container, BootstrapContext $context ): void {
		$definitions = $this->collect_lifecycle_definitions( $container, $context, 'activation' );
		if ( $definitions === [] ) {
			return;
		}

		$binder = $this->lifecycle_binder( $container );
		register_activation_hook(
			$this->filename,
			static function () use ( $binder, $definitions ): void {
				foreach ( $definitions as $definition ) {
					$binder->bind( $definition );
				}
			}
		);
	}

	private function register_deactivation_hook( ContainerInterface $container, BootstrapContext $context ): void {
		$definitions = $this->collect_lifecycle_definitions( $container, $context, 'deactivation' );
		if ( $definitions === [] ) {
			return;
		}

		$binder = $this->lifecycle_binder( $container );
		register_deactivation_hook(
			$this->filename,
			static function () use ( $binder, $definitions ): void {
				foreach ( $definitions as $definition ) {
					$binder->bind( $definition );
				}
			}
		);
	}

	/**
	 * @return Definition[]
	 */
	private function collect_lifecycle_definitions( ContainerInterface $container, BootstrapContext $context, string $method ): array {
		$definitions = [];

		foreach ( $this->modules as $module ) {
			$loader = $module->{$method}( $container, $context );
			foreach ( $loader->load() as $definition ) {
				$definitions[] = $definition;
			}
		}

		return $definitions;
	}

	private function lifecycle_binder( ContainerInterface $container ): CompositeBinder {
		return new CompositeBinder(
			new HookableBinder( $container ),
			new CallableBinder( $container )
		);
	}
}
