<?php
declare( strict_types=1 );

namespace WPDesk\Init\Module;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Binding\Loader\FilesystemDefinitions;
use WPDesk\Init\Bootstrap\BootstrapContext;
use WPDesk\Init\DependencyInjection\ContainerBuilder;

final class BuiltinModule extends AbstractModule {

	public function build( ContainerBuilder $builder, BootstrapContext $context ): void {
		$builder->add_definitions( __DIR__ . '/../Resources/services.inc.php' );
	}

	public function bindings( ContainerInterface $container, BootstrapContext $context ): BindingDefinitions {
		return new FilesystemDefinitions( __DIR__ . '/../Resources/bindings' );
	}
}
