<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Dumper;

class PhpFileDumperTest extends \WPDesk\Init\Tests\TestCase {

	public function test_dump_php_file() {
		$dir    = $this->initTempPlugin();
		$dumper = new \WPDesk\Init\Dumper\PhpFileDumper();
		$dumper->dump( [ 'foo' => 'bar' ], $dir . '/dump.php' );
		$this->assertFileExists( $dir . '/dump.php' );
		$content = include $dir . '/dump.php';

		$this->assertEquals( [ 'foo' => 'bar' ], $content );
	}

}