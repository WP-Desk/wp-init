<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Loader;

use WPDesk\Init\Util\PhpFileLoader;

class PhpFileLoaderTest extends \WPDesk\Init\Tests\TestCase {

	public function test_load_php_file(): void {
		$loader   = new PhpFileLoader();
		$resource = __DIR__ . '/../Fixtures/load.php';
		$data     = $loader->load( $resource );
		$this->assertEquals( [ 'foo' => 'bar' ], $data );
	}

}
