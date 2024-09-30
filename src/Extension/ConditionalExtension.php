<?php

declare(strict_types=1);

namespace WPDesk\Init\Extension;

use DI\Definition\Helper\AutowireDefinitionHelper;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Extension\CommonBinding\RequirementsCheck;
use WPDesk\Init\Extension\CommonBinding\WPDeskLicenseBridge;
use WPDesk\Init\Extension\CommonBinding\WPDeskTrackerBridge;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\Logger\SimpleLoggerFactory;

class ConditionalExtension implements Extension {

	public function bindings( ContainerInterface $c ): BindingDefinitions {
		$bindings = [];

		if ( class_exists( \WPDesk_Basic_Requirement_Checker::class ) ) {
			$bindings[] = [
				'priority' => -10,
				'handler'  => RequirementsCheck::class,
			];
		}

		if ( class_exists( \WPDesk\License\LicenseServer\PluginRegistrator::class ) ) {
			$bindings[] = WPDeskLicenseBridge::class;
		}

		if ( class_exists( \WPDesk_Tracker::class ) ) {
			$bindings[] = WPDeskTrackerBridge::class;
		}

		return new ArrayDefinitions( $bindings );
	}

	public function build( ContainerBuilder $builder, Plugin $plugin, ReadableConfig $config ): void {
		$definitions = [];

		if ( class_exists( \WPDesk_Basic_Requirement_Checker::class ) ) {
			$definitions[ RequirementsCheck::class ] = new AutowireDefinitionHelper();
		}

		if ( class_exists( \WPDesk\License\LicenseServer\PluginRegistrator::class ) ) {
			$definitions[ WPDeskLicenseBridge::class ] = ( new AutowireDefinitionHelper() )
				->constructorParameter( 'proudct_id', $config->get( 'product_id' ) )
				->constructorParameter( 'shops', (array) $config->get( 'shops', [] ) );
		}

		if ( class_exists( \WPDesk_Tracker::class ) ) {
			$definitions[ WPDeskTrackerBridge::class ] = new AutowireDefinitionHelper();
		}

		if ( class_exists( \WPDesk\Logger\SimpleLoggerFactory::class ) ) {
			$definitions[ LoggerInterface::class ] = static function ( ContainerInterface $c ) {
				$p = $c->get( Plugin::class );

				return ( new SimpleLoggerFactory(
					$p->get_slug(),
					[
						'level'        => $c->has( 'logger.level' ) ? $c->get( 'logger.level' ) : 'debug',
						'action_level' => $c->has( 'logger.action_level' ) ? $c->get( 'logger.action_level' ) : null,
					]
				) )->getLogger();
			};
		}

		$builder->add_definitions( $definitions );
	}
}
