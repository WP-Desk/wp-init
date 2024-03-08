<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use WPDesk\Init\Plugin\Plugin;
use WPDesk\Init\Kernel;
use Brain\Monkey;

class PluginInitTest extends TestCase {

	protected function setUp(): void {
		Monkey\setUp();
		Monkey\Functions\when('plugin_dir_path')->alias('dirname');
		parent::setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function xtest_initialization(): void {
		$this->initTempPlugin('simple-plugin');

		(new Kernel([]))->boot();
	}

	private function load_plugin_file( $dir, $slug ): ?Plugin {
		$load = \Closure::bind( static function () use ( $dir, $slug ): ?Plugin {
			require $dir . "/$slug.php";

			return $plugin;
		}, null, null );

		return $load();
	}
}
