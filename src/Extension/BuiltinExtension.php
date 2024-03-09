<?php

declare(strict_types=1);

namespace WPDesk\Init\Extension;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\DefinitionFactory;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Binding\Loader\FilesystemDefinitions;
use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Loader\PhpFileLoader;
use WPDesk\Init\Plugin\Plugin;

class BuiltinExtension implements Extension {

	public function bindings(ContainerInterface $c): BindingDefinitions {
		return new FilesystemDefinitions(
			__DIR__ . '/../Resources/bindings',
			new PhpFileLoader(),
			new DefinitionFactory()
	   	);
	}

	public function build( ContainerBuilder $builder, Plugin $plugin, ReadableConfig $config ): void {
		$builder->add_definitions( __DIR__ . '/../Resources/services.inc.php' );
	}
}
