<?php
declare( strict_types=1 );

namespace WPDesk\Init\Bundle;

use WPDesk\Migrations\Migrator;

class MigrationSubscriber implements \WPDesk\WPHook\HookSubscriber\HookSubscriber {

	/** @var Migrator */
	private $migrator;

	public function __construct(Migrator $migrator) {
		$this->migrator = $migrator;
	}

	public static function register(): iterable {
		yield 'plugins_loaded' => 'migrate';
	}

	public function migrate(): void {
		$this->migrator->migrate();
	}
}