<?php

namespace WPDesk\Init\Tests\Util;

use WPDesk\Init\Tests\TestCase;
use WPDesk\Init\Util\Path;

class PathTest extends TestCase {

	public function test_canonical_path(): void
    {
        $path = new Path('src/unit/../etc');
        $this->assertEquals('src/etc', (string) $path->canonical());
    }

    public function test_absolute_path(): void
    {
        $path = new Path('src');
        $this->assertEquals(getcwd().'/src', (string) $path->absolute());
    }

    public function test_join(): void
    {
        $path = new Path('/var/www');
        $joinedPath = $path->join('public', 'html', 'index.php');
        $this->assertEquals('/var/www/public/html/index.php', (string) $joinedPath);
    }

    public function test_get_basename(): void
    {
        $path = new Path('/var/www/public/html/index.php');
        $this->assertEquals('index.php', $path->get_basename());
    }

    public function test_get_filename_without_extension(): void
    {
        $path = new Path('/var/www/public/html/index.php');
        $this->assertEquals('index', $path->get_filename_without_extension());
    }

    public function test_read_directory(): void
    {
        $path = new Path(__DIR__ . '/../Fixtures/hook-bindings/');
        $dirContent = $path->read_directory();
        $this->assertEquals(getcwd() . '/tests/Fixtures/hook-bindings/index.php', (string) $dirContent[0]);
    }

}
