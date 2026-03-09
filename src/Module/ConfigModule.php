<?php
declare( strict_types=1 );

namespace WPDesk\Init\Module;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Binding\Loader\EmptyDefinitions;
use WPDesk\Init\Binding\Loader\FilesystemDefinitions;
use WPDesk\Init\Bootstrap\BootGate;
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
		$hooks_path = $context->config()->get( 'hooks' );
		if ( $hooks_path === null ) {
			return new EmptyDefinitions();
		}

		return new FilesystemDefinitions(
			( new Path( (string) $hooks_path ) )->absolute( $context->plugin()->get_path() )
		);
	}

	public function activation( ContainerInterface $container, BootstrapContext $context ): BindingDefinitions {
		$activation = $context->config()->get( 'activation' );
		if ( $activation === null ) {
			return new EmptyDefinitions();
		}

		return new ArrayDefinitions( is_array( $activation ) ? $activation : [ $activation ] );
	}

	public function deactivation( ContainerInterface $container, BootstrapContext $context ): BindingDefinitions {
		$deactivation = $context->config()->get( 'deactivation' );
		if ( $deactivation === null ) {
			return new EmptyDefinitions();
		}

		return new ArrayDefinitions( is_array( $deactivation ) ? $deactivation : [ $deactivation ] );
	}

	public function gates( ContainerInterface $container, BootstrapContext $context ): array {
		$gates = [];

		foreach ( (array) $context->config()->get( 'gates', [] ) as $gate_class ) {
			if ( ! is_string( $gate_class ) || $gate_class === '' ) {
				throw new \LogicException( 'Configured gates must be class-string identifiers.' );
			}

			$gate = $container->get( $gate_class );
			if ( ! $gate instanceof BootGate ) {
				throw new \LogicException( sprintf( 'Configured gate "%s" must implement %s.', $gate_class, BootGate::class ) );
			}

			$gates[] = $gate;
		}

		return $gates;
	}
}
