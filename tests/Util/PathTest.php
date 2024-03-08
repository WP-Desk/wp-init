<?php

namespace WPDesk\Init\Tests\Util;

use WPDesk\Init\Tests\TestCase;
use WPDesk\Init\Util\Path;

class PathTest extends TestCase {

	public function test_canonical_path(): void {
		$path = new Path('src');
		$this->assertEquals(getcwd().'/src', (string) $path->absolute());
	}

	public function test_join(): void {
		$path = new Path('src');
		$this->assertEquals('src/test/unit', (string) $path->join('test', 'unit'));
		$this->assertEquals(getcwd().'/src/Util', (string) $path->join('test', '..', 'Util')->absolute());
	}
}
