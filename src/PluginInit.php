<?php
declare( strict_types=1 );

namespace WPDesk\Init;

use DI\ContainerBuilder;
use InvalidArgumentException;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\WPHook\ContainerSubscriberResolver;
use WPDesk\WPHook\HookListenerProvider;
use WPDesk\WPHook\HookSubscriber\HookSubscriber;

/**
 * Plugin builder class responsible for our initialization system.
 */
final class PluginInit {
	private const ENV_PRODUCTION = 'prod';
	private const ENV_DEVELOPMENT = 'dev';

	private $bundles = [];

	/** @var string|null Plugin filename. */
	private $filename;

	/** @var Configuration */
	private $config;

	/** @var string */
	private $env;

	/** @var class-string<HookSubscriber>[] */
	private $subscribers;

	public static function from_config(
		string $config_path,
		string $environment = null
	): self {
		$config = require $config_path;

		return new self( new Configuration( $config ), $environment ?? '' );
	}

	public function __construct(
		Configuration $config,
		string $environment = self::ENV_PRODUCTION
	) {
		$this->config = $config;
		$this->env    = $environment;
	}

	/**
	 * Explicitly set name of main plugin file used for reference. This value should be set with
	 * caution, as filename is the most important identifier, which allows us to retreive further
	 * plugin data, such as basename, plugin dir and url. By default, this value is set to
	 * original caller's file name.
	 */
	public function set_filename( string $filename ): self {
		$this->filename = $filename;

		return $this;
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
			$backtrace      = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 1 );
			$this->filename = $backtrace[0]['file'];
		}

		$data        = new PluginHeaderParser();
		$plugin_data = $data->get_plugin_data( $this->filename );

//		$this->createCompilationDirectory(dirname($this->config['cache_path']));
//		$this->writeFileAtomic(
//			dirname($this->filename).'/'.$this->config['cache_path'].'/plugin.php',
//			'<?php return ' . var_export($plugin_data)
//		);

		$plugin = $this->create_plugin( $plugin_data );

		if ( ! isset( $this->config['require']['wp'] ) ) {
			$this->config['require']['wp'] = $plugin_data['RequiresWP'];
		}

		if ( ! isset( $this->config['require']['php'] ) ) {
			$this->config['require']['php'] = $plugin_data['RequiresPHP'];
		}

		if ( ! $this->requirements_met( $plugin ) ) {
			return null;
		}

		foreach ( $this->config['bundles'] ?? [] as $bundle ) {
			$this->bundles[ $bundle ] = new $bundle();
		}

		$this->subscribers = $this->config->get( 'hook_subscribers', [] );

		foreach ( $this->bundles as $bundle ) {
			$this->subscribers = array_merge( $this->subscribers, $bundle->get_subscribers() );
		}

		$builder = $this->build_container( $plugin );

		foreach ( $this->bundles as $bundle ) {
			$bundle->build( $builder, $this->config );
		}

		$container = $builder->build();

		$container->set( Plugin::class, $plugin );

		$provider = new HookListenerProvider( new ContainerSubscriberResolver( $container ) );

		foreach ( $this->subscribers as $subscriber ) {
			$provider->subscribe( $subscriber );
		}

		return $plugin;
	}

	private function build_container( Plugin $plugin ): ContainerBuilder {
		$builder = new ContainerBuilder();

		if ( $plugin->is_environment( self::ENV_PRODUCTION ) ) {
			$builder->enableCompilation(
				$plugin->get_path( $this->config['cache_path'] . '/container' ),
				str_replace( '-', '_', $plugin->get_slug() ) . '_container'
			);
		}

		$builder->addDefinitions(
			[
				\wpdb::class => static function () {
					global $wpdb;

					return $wpdb;
				}
			]
		);

		return $builder;
	}

	private function create_plugin( array $plugin_data ): Plugin {
		return new Plugin(
			$this->filename,
			$plugin_data['Name'],
			$plugin_data['Version'],
			$plugin_data['TextDomain'],
			$this->env
		);
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @return bool
	 */
	private function requirements_met( Plugin $plugin ): bool {
		$requirements_factory = new \WPDesk_Basic_Requirement_Checker_Factory();
		$requirements         = $requirements_factory->create_from_requirement_array(
			$plugin->get_basename(),
			$plugin->get_name(),
			$this->config['require'],
			$plugin->get_slug()
		);

		if ( ! $requirements->are_requirements_met() ) {
			$requirements->render_notices();

			return false;
		}

		return true;
	}

	private function createCompilationDirectory( string $directory ): void {
		if ( ! is_dir( $directory ) && ! @mkdir( $directory, 0777, true ) && ! is_dir( $directory ) ) {
			throw new InvalidArgumentException( sprintf( 'Compilation directory does not exist and cannot be created: %s.', $directory ) );
		}
		if ( ! is_writable( $directory ) ) {
			throw new InvalidArgumentException( sprintf( 'Compilation directory is not writable: %s.', $directory ) );
		}
	}

	private function writeFileAtomic( string $fileName, string $content ): void {
		$tmpFile = @tempnam( dirname( $fileName ), 'swap-compile' );
		if ( $tmpFile === false ) {
			throw new InvalidArgumentException(
				sprintf( 'Error while creating temporary file in %s', dirname( $fileName ) )
			);
		}
		@chmod( $tmpFile, 0666 );

		$written = file_put_contents( $tmpFile, $content );
		if ( $written === false ) {
			@unlink( $tmpFile );

			throw new InvalidArgumentException( sprintf( 'Error while writing to %s', $tmpFile ) );
		}

		@chmod( $tmpFile, 0666 );
		$renamed = @rename( $tmpFile, $fileName );
		if ( ! $renamed ) {
			@unlink( $tmpFile );
			throw new InvalidArgumentException( sprintf( 'Error while renaming %s to %s', $tmpFile, $fileName ) );
		}
	}

}
