<?php
declare( strict_types=1 );

namespace WPDesk\Init\Module;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Binding\Loader\FilesystemDefinitions;
use WPDesk\Init\Bootstrap\BootstrapContext;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Util\Path;

final class ConfigModule implements Module {

	public function build( ContainerBuilder $builder, BootstrapContext $context ): void {
		$services = array_map(
			static function ( string $service ) use ( $context ): string {
				return (string) ( new Path( $service ) )->absolute( $context->plugin()->get_path() );
			},
			(array) $context->config()->get( 'services', [] )
		);

		$builder->add_definitions( ...$services );
	}

	public function bindings( ContainerInterface $container, BootstrapContext $context ): BindingDefinitions {
		$hooks_path = $context->config()->get( 'hooks', $context->config()->get( 'hook_resources_path' ) );
		if ( $hooks_path === null ) {
			return new ArrayDefinitions( [] );
		}

		return new FilesystemDefinitions(
			( new Path( (string) $hooks_path ) )->absolute( $context->plugin()->get_path() )
		);
	}

	public function gates( ContainerInterface $container, BootstrapContext $context ): array {
		return [];
	}
}
