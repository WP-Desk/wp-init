<?php

declare(strict_types=1);

namespace WPDesk\Init\Extension;

use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Plugin\Plugin;

class LoggerExtension {

	public function build( ContainerBuilder $builder, Plugin $plugin, ReadableConfig $config ): void {
		$builder->add_definitions(
			[
				LoggerInterface::class => new NullLogger(),
			]
		);
	}
}
