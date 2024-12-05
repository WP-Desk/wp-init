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
use WPDesk\Init\Binding\Loader\ClusteredLoader;
use WPDesk\Init\Binding\Loader\CompositeBindingLoader;
use WPDesk\Init\Binding\Loader\DebugBindingLoader;
use WPDesk\Init\Binding\Loader\OrderedBindingLoader;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Extension\ExtensionsSet;
use WPDesk\Init\HookDriver\CompositeDriver;
use WPDesk\Init\HookDriver\GenericDriver;
use WPDesk\Init\HookDriver\HookDriver;
use WPDesk\Init\HookDriver\LegacyDriver;
use WPDesk\Init\Util\PhpFileDumper;
use WPDesk\Init\Util\PhpFileLoader;
use WPDesk\Init\Plugin\Header;
use WPDesk\Init\Util\Path;
use WPDesk\Init\Plugin\DefaultHeaderParser;
use WPDesk\Init\Plugin\HeaderParser;
use WPDesk\Init\Plugin\Plugin;

final class Kernel {

	/** @var string|null Plugin filename. */
	private ?string $filename;

	private Configuration $config;

	private PhpFileLoader $loader;

	private HeaderParser $parser;

	private ExtensionsSet $extensions;

	private PhpFileDumper $dumper;

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
		$this->dumper     = new PhpFileDumper();
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

		$plugin = new Plugin( $this->filename, new Header( $plugin_data ) );

		$container = $this->initialize_container( $plugin );
		$container->set( Plugin::class, $plugin );
		$container->set( Configuration::class, $this->config );

		$this->prepare_driver( $container )->register_hooks();
	}

	private function get_cache_path( string $path = '' ): string {
		return (string) ( new Path( $this->config->get( 'cache_path', 'generated' ) ) )->join( $path )->absolute(
			rtrim( plugin_dir_path( $this->filename ), '/' ) . '/'
		);
	}

	private function get_container_name( Plugin $plugin ): string {
		return preg_replace( '/[^\w_]/', '_', implode("_", [ $plugin->get_slug(), $plugin->get_version(), 'container' ]) );
	}

	private function initialize_container( Plugin $plugin, bool $use_cache = true ): Container {
		$original_builder = new DiBuilder();

		if ( $this->is_prod() && $use_cache ) {
			$original_builder->enableCompilation(
				$this->get_cache_path(),
				$this->get_container_name( $plugin )
			);
		}

		$builder = new ContainerBuilder( $original_builder );

		if ( ! function_exists( 'WPDesk\Init\DI\create' ) ) {
			require __DIR__ . '/di-functions.php';
		}

		foreach ( $this->extensions as $extension ) {
			$extension->build( $builder, $plugin, $this->config );
		}

		try {
			return $builder->build();
		} catch ( \InvalidArgumentException $e ) {
			if ( $use_cache === false ) {
				// It means, that saving to disk was not an issue.
				throw $e;
			}

			return $this->initialize_container( $plugin, false );
		}
	}

	private function prepare_driver( ContainerInterface $container ): HookDriver {
		$loader = new CompositeBindingLoader();
		foreach ( $this->extensions as $extension ) {
			$loader->add( $extension->bindings( $container ) );
		}

		$loader = new OrderedBindingLoader(
			new ClusteredLoader( $loader )
		);

		if ( $this->is_dev() ) {
			$loader = new DebugBindingLoader( $loader );
		}

		$driver = new GenericDriver(
			$loader,
			new CompositeBinder(
				new StoppableBinder( new HookableBinder( $container ), $container ),
				new CallableBinder( $container )
			)
		);

		if ( $this->config->get( 'legacy', false ) ) {
			$driver = new CompositeDriver(
				$driver,
				new LegacyDriver( $container )
			);
		}

		return $driver;
	}

	private function is_dev(): bool {
		return $this->config->get( 'debug', false ) || wp_get_environment_type() !== 'development';
	}

	private function is_prod(): bool {
		return $this->is_dev() === false;
	}
}
