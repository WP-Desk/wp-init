<?php
declare( strict_types=1 );

namespace WPDesk\Init\Bundle;

use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\Plugin;
use WPDesk\Migrations\Migrator;
use WPDesk\Migrations\WpdbMigrator;

class MigrationsBundle {

	public function build( ContainerBuilder $builder, ReadableConfig $config ): void {
		$builder->addDefinitions( [
			Migrator::class => function ( Plugin $plugin ) use ( $config ) {
				return WpdbMigrator::from_directories(
					$config->get( 'migrations', [] ),
					$config->get( 'migrations_table_name', $plugin->get_slug() . 'migrations' )
				);
			}
		] );
	}

	public static function subscribers(): iterable {
		return [
			MigrationSubscriber::class
		];
	}

}