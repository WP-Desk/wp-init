<?php
declare( strict_types=1 );

namespace WPDesk\Init;

use DI\ContainerBuilder;
use DI\Definition\Source\DefinitionSource;
use Psr\Container\ContainerInterface;

/**
 * Plugin builder class responsible for our initialization system.
 */
final class PluginInit {

	/** @var array */
	private $requirements = [];

	/** @var array Dependency injection container definitions */
	private $definitions = [];

	/** @var string|null Plugin filename. */
	private $filename;

	/** @var string|null Plugin identifier. */
	private $slug;

	/**
	 * Define environment constraints required by a plugin. If requirements are not fulfilled,
	 * the plugin will not be able to instantiate.
	 *
	 * @param array $requirements Validation rules are defined according to `wp-basic-requirements`
	 * documentation.
	 *
	 * @see https://gitlab.wpdesk.dev/wpdesk/wp-basic-requirements
	 */
	public function set_requirements( array $requirements ): self {
		$this->requirements = $requirements;

		return $this;
	}

	/**
	 * Define plugin slug. This value will be used as main plugin identifier and as translation
	 * text domain. By default, plugin slug is set to plugin directory name.
	 */
	public function set_slug( string $slug ): self {
		$this->slug = $slug;

		return $this;
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
	 * Add definition sources to use in dependency injection container.
	 * If plugin doesn't receive any definitions, then container build is skipped.
	 *
	 * @see https://php-di.org/doc/container-configuration.html
	 *
	 * @param array|DefinitionSource|string $definitions
	 */
	public function add_container_definitions( ...$definitions ): self {
		$this->definitions = array_merge( $this->definitions, $definitions );

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
			$backtrace      = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );
			$this->filename = $backtrace[0]['file'];
		}

		$plugin = $this->create_plugin();

		if (
			! empty( $this->requirements ) &&
			class_exists( \WPDesk_Basic_Requirement_Checker_Factory::class ) &&
			! $this->requirements_met( $plugin )
		) {
			return null;
		}

		if ( ! empty( $this->definitions ) && class_exists( ContainerBuilder::class ) ) {
			$plugin->set_container( $this->build_container( $plugin ) );
		}

		return $this->register_default_providers( $plugin );
	}

	private function build_container( Plugin $plugin ): ContainerInterface {
		$builder = new ContainerBuilder();

		if ( ! getenv( 'WPDESK_DEVELOPMENT' ) ) {
			$builder->enableCompilation(
				$plugin->get_path( '/cache' ),
				str_replace( '-', '_', $plugin->get_slug() ) . '_container'
			);
		}

		$builder->addDefinitions( ...$this->definitions );

		return $builder->build();
	}

	private function register_default_providers( Plugin $plugin ): Plugin {
		$plugin->register_hooks(
			new HookProvider\I18n(),
			new HookProvider\WooCommerceHPOSCompatibility(),
			new HookProvider\ActivationDate()
		);

		if ( ! empty( $this->definitions ) ) {
			$plugin->register_hooks( new HookProvider\ContainerHookProvider() );
		}

		return $plugin;
	}


	private function create_plugin(): Plugin {
		$plugin = new Plugin();
		$plugin->set_file( $this->filename )
		       ->set_basename( plugin_basename( $this->filename ) )
		       ->set_directory( plugin_dir_path( $this->filename ) )
		       ->set_url( plugin_dir_url( $this->filename ) )
		       ->set_slug( $this->slug ?? basename( $plugin->get_directory() ) );

		return $plugin;
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
			$this->requirements,
			$plugin->get_slug()
		);

		if ( ! $requirements->are_requirements_met() ) {
			$requirements->render_notices();

			return false;
		}

		return true;
	}

}
