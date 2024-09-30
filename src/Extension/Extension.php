<?php
declare(strict_types=1);

namespace WPDesk\Init\Extension;

use Psr\Container\ContainerInterface;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Plugin\Plugin;

interface Extension {

	public function build( ContainerBuilder $builder, Plugin $plugin, ReadableConfig $config ): void;

	public function bindings( ContainerInterface $c ): BindingDefinitions;
}
