<?php
declare( strict_types=1 );

namespace WPDesk\Init;

use DI\Container;
use DI\ContainerBuilder as DiBuilder;
use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Binder\CallableBinder;
use WPDesk\Init\Binding\Binder\CompositeBinder;
use WPDesk\Init\Binding\Binder\HookableBinder;
use WPDesk\Init\Binding\Binder\StoppableBinder;
use WPDesk\Init\Binding\Loader\CompositeBindingLoader;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Extension\ExtensionsSet;
use WPDesk\Init\HookDriver\CompositeDriver;
use WPDesk\Init\HookDriver\GenericDriver;
use WPDesk\Init\HookDriver\HookDriver;
use WPDesk\Init\HookDriver\LegacyHookableDriver;
use WPDesk\Init\Loader\PhpFileLoader;
use WPDesk\Init\Plugin\Header;
use WPDesk\Init\Util\Path;
use WPDesk\Init\Plugin\DefaultHeaderParser;
use WPDesk\Init\Plugin\HeaderParser;
use WPDesk\Init\Plugin\Plugin;

final class Kernel {

	/** @var string|null Plugin filename. */
	private $filename;

	/** @var Configuration */
	private $config;

	/** @var PhpFileLoader */
	private $loader;

	/** @var HeaderParser */
	private $parser;

	/** @var ExtensionsSet */
	private $extensions;

	public function __construct(
		string $filename,
		Configuration $config,
		ExtensionsSet $extensions
	) {
		$this->filename   = $filename;
		$this->config     = $config;
		$this->extensions = $extensions;
		$this->loader     = new PhpFileLoader();
		$this->parser     = new DefaultHeaderParser();
	}

	public function boot(): void {
		$cache_path = $this->get_cache_path( 'plugin.php' );
		try {
			$plugin_data = $this->loader->load( $cache_path );
		} catch ( \Exception $e ) {
			// If cache not found, load data from memory.
			// Avoid writing files on host environment.
			// Generate cache with command instead.
			$plugin_data = $this->parser->parse( $this->filename );
		}

		$plugin = new Plugin( $this->filename, new Header( $plugin_data ) );

		$container = $this->initialize_container( $plugin );
		$container->set( Plugin::class, $plugin );
		$container->set( Configuration::class, $this->config );

		$driver = $this->prepare_driver( $container );
		$driver->register_hooks( $this->config, $container );
	}

	private function get_cache_path( string $path = '' ): string {
		return (string) ( new Path( $this->config->get( 'cache_path', 'generated' ) ) )->join( $path )->absolute(
			rtrim( plugin_dir_path( $this->filename ), '/' ) . '/'
		);
	}

	private function get_container_name( Plugin $plugin ): string {
		return str_replace( '-', '_', $plugin->get_slug() ) . '_container';
	}

	private function initialize_container( Plugin $plugin ): Container {
		$original_builder = new DiBuilder();
		$original_builder->enableCompilation(
			$this->get_cache_path(),
			$this->get_container_name( $plugin )
		);

		if ( file_exists( $this->get_cache_path( $this->get_container_name( $plugin ) . '.php' ) ) ) {
			return $original_builder->build();
		}

		$builder = new ContainerBuilder( $original_builder );

		foreach ( $this->extensions as $extension ) {
			$extension->build( $builder, $plugin, $this->config );
		}

		return $builder->build();
	}

	private function prepare_driver( ContainerInterface $container ): HookDriver {
		$loader = new CompositeBindingLoader();
		foreach ( $this->extensions->bindings( $container ) as $bindings ) {
			$loader->add( $bindings );
		}

		$driver = new GenericDriver(
			$loader,
			new StoppableBinder(
				new CompositeBinder(
					new HookableBinder( $container ),
					new CallableBinder( $container )
				),
				$container
			)
		);

		if ( class_exists( \WPDesk_Plugin_Info::class ) ) {
			$driver = new CompositeDriver(
				$driver,
				new LegacyHookableDriver( $container )
			);
		}

		return $driver;
	}
}
