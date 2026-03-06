<?php
declare( strict_types=1 );

namespace WPDesk\Init\Module;

use DI\Definition\Helper\AutowireDefinitionHelper;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Binding\Loader\EmptyDefinitions;
use WPDesk\Init\Bootstrap\BootstrapContext;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Extension\CommonBinding\WPDeskTrackerBridge;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\Logger\SimpleLoggerFactory;

final class WPDeskTrackerModule implements Module {

	public function build( ContainerBuilder $builder, BootstrapContext $context ): void {
		if ( ! class_exists( \WPDesk_Tracker::class ) ) {
			throw new \LogicException( 'WPDeskTrackerModule requires the WP Desk tracker package to be installed.' );
		}

		$definitions = [
			WPDeskTrackerBridge::class => new AutowireDefinitionHelper(),
		];

		if ( class_exists( \WPDesk\Logger\SimpleLoggerFactory::class ) ) {
			$definitions[ LoggerInterface::class ] = static function ( ContainerInterface $container ) {
				$plugin = $container->get( Plugin::class );

				return ( new SimpleLoggerFactory(
					$plugin->get_slug(),
					[
						'level'        => $container->has( 'logger.level' ) ? $container->get( 'logger.level' ) : 'debug',
						'action_level' => $container->has( 'logger.action_level' ) ? $container->get( 'logger.action_level' ) : null,
					]
				) )->getLogger();
			};
		}

		$builder->add_definitions( $definitions );
	}

	public function bindings( ContainerInterface $container, BootstrapContext $context ): BindingDefinitions {
		return new ArrayDefinitions(
			[
				'plugins_loaded' => WPDeskTrackerBridge::class,
			]
		);
	}

	public function activation( ContainerInterface $container, BootstrapContext $context ): BindingDefinitions {
		return new EmptyDefinitions();
	}

	public function deactivation( ContainerInterface $container, BootstrapContext $context ): BindingDefinitions {
		return new EmptyDefinitions();
	}

	public function gates( ContainerInterface $container, BootstrapContext $context ): array {
		return [];
	}
}
