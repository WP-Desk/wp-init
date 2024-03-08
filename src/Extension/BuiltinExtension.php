<?php

declare(strict_types=1);

namespace WPDesk\Init\Extension;

use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Plugin\Plugin;

class BuiltinExtension implements Extension {

	public function build( ContainerBuilder $builder, Plugin $plugin, ReadableConfig $config ): void {
		$builder->add_definitions( __DIR__ . '/../Resources/services.inc.php' );
	}
}
