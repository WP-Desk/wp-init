<?php
declare( strict_types=1 );

namespace WPDesk\Init\Module;

use DI\Definition\Helper\AutowireDefinitionHelper;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Binding\Loader\BindingDefinitions;
use WPDesk\Init\Bootstrap\BootstrapContext;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Extension\CommonBinding\WPDeskLicenseBridge;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\Logger\SimpleLoggerFactory;

final class WPDeskLicenseModule implements Module {

	public function build( ContainerBuilder $builder, BootstrapContext $context ): void {
		if ( ! class_exists( \WPDesk\License\LicenseServer\PluginRegistrator::class ) ) {
			throw new \LogicException( 'WPDeskLicenseModule requires "wpdesk/wp-wpdesk-license" to be installed.' );
		}

		$config = $context->module_config( self::class );
		$product_id = $config['product_id'] ?? $context->config()->get( 'product_id' );
		if ( ! is_string( $product_id ) || $product_id === '' ) {
			throw new \LogicException( 'WPDeskLicenseModule requires "product_id" in module config.' );
		}

		$definitions = [
			WPDeskLicenseBridge::class => ( new AutowireDefinitionHelper() )
				->constructorParameter( 'product_id', $product_id )
				->constructorParameter( 'shops', (array) ( $config['shops'] ?? $context->config()->get( 'shops', [] ) ) ),
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
				WPDeskLicenseBridge::class,
			]
		);
	}

	public function gates( ContainerInterface $container, BootstrapContext $context ): array {
		return [];
	}
}
