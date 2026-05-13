<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Module;

use Brain\Monkey;
use DI\ContainerBuilder as DiContainerBuilder;
use Psr\Container\ContainerInterface;
use WPDesk\Init\Bootstrap\BootstrapContext;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\DependencyInjection\ContainerBuilder;
use WPDesk\Init\Module\AbstractModule;
use WPDesk\Init\Plugin\Header;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\Init\Tests\TestCase;

final class AbstractModuleTest extends TestCase {

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

	public function test_defaults_are_empty(): void {
		$module    = new class() extends AbstractModule {};
		$builder   = new ContainerBuilder( new DiContainerBuilder() );
		$container = $this->createMock( ContainerInterface::class );
		$context   = new BootstrapContext(
			new Plugin( '/tmp/plugin/plugin.php', new Header( [ 'Name' => 'Example', 'Version' => '1.0.0' ] ) ),
			new Configuration( [] ),
			[],
			'production',
			false
		);

		$module->build( $builder, $context );

		$this->assertSame( [], iterator_to_array( $module->bindings( $container, $context )->load() ) );
		$this->assertSame( [], iterator_to_array( $module->activate( $container, $context )->load() ) );
		$this->assertSame( [], iterator_to_array( $module->deactivate( $container, $context )->load() ) );
		$this->assertSame( [], $module->gates( $container, $context ) );
	}
}
