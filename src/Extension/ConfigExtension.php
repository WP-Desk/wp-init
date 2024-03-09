<?php

declare(strict_types=1);

namespace WPDesk\Init\Extension;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\DefinitionFactory;
use WPDesk\Init\Binding\Loader\ArrayBindingLoader;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Binding\Loader\DirectoryBasedLoader;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Loader\PhpFileLoader;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\Init\Util\Path;

class ConfigExtension implements Extension {

	public function bindings( ContainerInterface $c ): BindingDefinitions {
		$config = $c->get( Configuration::class );
		if ( $config->has( 'hook_resources_path' ) ) {
			return new DirectoryBasedLoader(
				( new Path( $config->get( 'hook_resources_path' ) ) )->absolute( $c->get( Plugin::class )->get_path() )
			);
		}

		return new ArrayBindingLoader( [] );
	}

	public function build( ContainerBuilder $builder, Plugin $plugin, ReadableConfig $config ): void {
		$services = array_map(
			function ( $service ) use ( $plugin ) {
				return (string) ( new Path( $service ) )->absolute( $plugin->get_path() );
			},
			(array) $config->get( 'services', [] )
		);

		$builder->add_definitions( ...$services );
	}
}
