<?php
declare( strict_types=1 );

namespace WPDesk\Init\Module;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Binding\Loader\EmptyDefinitions;
use WPDesk\Init\Bootstrap\BootstrapContext;
use WPDesk\Init\Bootstrap\RequirementsGate;
use WPDesk\Init\DependencyInjection\ContainerBuilder;

final class RequirementsModule implements Module {

	public function build( ContainerBuilder $builder, BootstrapContext $context ): void {
		if ( ! class_exists( \WPDesk_Basic_Requirement_Checker::class ) ) {
			throw new \LogicException( 'RequirementsModule requires "wpdesk/wp-basic-requirements" to be installed.' );
		}
	}

	public function bindings( ContainerInterface $container, BootstrapContext $context ): BindingDefinitions {
		return new ArrayDefinitions( [] );
	}

	public function activation( ContainerInterface $container, BootstrapContext $context ): BindingDefinitions {
		return new EmptyDefinitions();
	}

	public function deactivation( ContainerInterface $container, BootstrapContext $context ): BindingDefinitions {
		return new EmptyDefinitions();
	}

	public function gates( ContainerInterface $container, BootstrapContext $context ): array {
		$config       = $context->module_config( self::class );
		$requirements = isset( $config['requirements'] ) ? $config['requirements'] : [];

		return [ new RequirementsGate( $context->plugin(), (array) $requirements ) ];
	}
}
