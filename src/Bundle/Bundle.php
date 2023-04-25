<?php
declare( strict_types=1 );

namespace WPDesk\Init\Bundle;

use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\DependencyInjection\ContainerBuilder;

interface Bundle {

	public function build( ContainerBuilder $container_builder, ReadableConfig $config ): void;

}