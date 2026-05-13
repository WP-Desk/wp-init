<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use Brain\Monkey;
use WPDesk\Init\Bootstrap\BootstrapContext;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\Plugin\Header;
use WPDesk\Init\Plugin\Plugin;

final class BootstrapContextTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Monkey\Functions\when( 'plugin_basename' )->returnArg();
		Monkey\Functions\when( 'plugin_dir_path' )->alias( 'dirname' );
		Monkey\Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.org/plugin/' );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_module_config_defaults_to_empty_array(): void {
		$context = new BootstrapContext(
			new Plugin( '/tmp/plugin/plugin.php', new Header( [ 'Name' => 'Example', 'Version' => '1.0.0' ] ) ),
			new Configuration( [] ),
			[],
			'production',
			false
		);

		$this->assertSame( [], $context->module_config( 'Vendor\\Module' ) );
	}

	public function test_module_config_is_exposed_per_module(): void {
		$context = new BootstrapContext(
			new Plugin( '/tmp/plugin/plugin.php', new Header( [ 'Name' => 'Example', 'Version' => '1.0.0' ] ) ),
			new Configuration( [] ),
			[
				'Vendor\\Module' => [
					'flag' => true,
				],
			],
			'development',
			true
		);

		$this->assertSame( [ 'flag' => true ], $context->module_config( 'Vendor\\Module' ) );
		$this->assertTrue( $context->is_development() );
		$this->assertTrue( $context->is_debug() );
	}
}
