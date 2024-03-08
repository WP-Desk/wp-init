<?php

declare(strict_types=1);

namespace WPDesk\Init\Extension;

use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\Init\Util\Path;

class ConfigExtension implements Extension {

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
