<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Loader;

class PhpFileLoaderTest extends \WPDesk\Init\Tests\TestCase {

	public function test_load_php_file() {
		$loader   = new \WPDesk\Init\Loader\PhpFileLoader();
		$resource = __DIR__ . '/../Fixtures/load.php';
		$data     = $loader->load( $resource );
		$this->assertEquals( [ 'foo' => 'bar' ], $data );
	}

}