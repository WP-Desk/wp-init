<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use WPDesk\Init\Plugin;
use WPDesk\Init\PluginInit;
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

	public function test_minimal_init(): void {
		Monkey\Functions\stubs([
			'plugin_basename',
			'plugin_dir_url'
		]);

		$slug   = 'simple-plugin';
		$dir    = $this->initTempPlugin( $slug );
		$plugin = $this->load_plugin_file( $dir, $slug );

		$this->assertFileDoesNotExist( $dir . '/cache' );
		$this->assertEquals( 'simple-plugin', $plugin->get_slug() );
	}

	public function test_advanced_init(): void {
		Monkey\Functions\stubs([
			'plugin_basename',
			'plugin_dir_url'
		]);
		Monkey\Functions\expect('get_bloginfo')
			->with('version')
			->andReturn('5.6');

		$slug = 'advanced-plugin';
		$dir  = $this->initTempPlugin( $slug );

		$plugin = $this->load_plugin_file( $dir, $slug );

		$this->assertNotNull($plugin);
		$this->assertFileExists( $dir . '/generated/container/advanced_plugin_container.php' );
	}

	private function load_plugin_file( $dir, $slug ): ?Plugin {
		$load = \Closure::bind( static function () use ( $dir, $slug ): ?Plugin {
			require $dir . "/$slug.php";

			return $plugin;
		}, null, null );

		return $load();
	}
}