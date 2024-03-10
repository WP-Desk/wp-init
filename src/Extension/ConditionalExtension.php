<?php

declare(strict_types=1);

namespace WPDesk\Init\Extension;

use DI\Definition\Helper\AutowireDefinitionHelper;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use WPDesk\Init\Binding\Loader\ArrayBindingLoader;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Extension\CommonBinding\WPDeskLicenseBridge;
use WPDesk\Init\Extension\CommonBinding\WPDeskTrackerBridge;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\Logger\WC\WooCommerceHandler;

class ConditionalExtension implements Extension {

	public function bindings( ContainerInterface $c ): BindingDefinitions {
		$bindings = [];

		if ( class_exists( \WPDesk\License\PluginRegistrator::class ) ) {
			$bindings[] = WPDeskLicenseBridge::class;
		}

		if ( class_exists( \WPDesk_Tracker::class ) ) {
			$bindings[] = WPDeskTrackerBridge::class;
		}

		return new ArrayBindingLoader( $bindings );
	}

	public function build( ContainerBuilder $builder, Plugin $plugin, ReadableConfig $config ): void {
		$definitions = [];

		if ( class_exists( \WPDesk\License\PluginRegistrator::class ) ) {
			$definitions[ WPDeskLicenseBridge::class ] = new AutowireDefinitionHelper();
		}

		if ( class_exists( \WPDesk_Tracker::class ) ) {
			$definitions[ WPDeskTrackerBridge::class ] = new AutowireDefinitionHelper();
		}

		if ( class_exists( \WPDesk\Logger\WC\WooCommerceHandler::class ) ) {
			$definitions[ LoggerInterface::class ] = static function () use ( $plugin ) {
				$logger = new Logger(
					$plugin->get_slug(),
					[],
					[ new PsrLogMessageProcessor( null, true ), new UidProcessor() ]
				);

				$attach_handler = function () use ( $logger, $plugin ) {
					$handler = new WooCommerceHandler( wc_get_logger(), $plugin->get_slug() );
					$handler->setFormatter(
						new LineFormatter( '%channel%.%level_name% [%extra.uid%]: %message% %context% %extra%' )
					);
					$logger->pushHandler( $handler );
				};

				if ( \function_exists( 'wc_get_logger' ) ) {
					$attach_handler();
				} else {
					\add_action( 'woocommerce_init', $attach_handler );
				}

				return $logger;
			};
		}

		$builder->add_definitions( $definitions );
	}
}
