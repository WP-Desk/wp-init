<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Binding;

use WPDesk\Init\Binding\Loader\ArrayBindingLoader;
use WPDesk\Init\Binding\Loader\DirectoryBasedLoader;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\Loader\PhpFileLoader;
use WPDesk\Init\Tests\TestCase;

class DirectoryBasedLoaderTest extends TestCase {

	public function test_throws_when_configuration_entry_is_missing(): void {
		$this->expectException(\InvalidArgumentException::class);
		$a = new DirectoryBasedLoader(new Configuration([]), new PhpFileLoader());
		$a->load();
	}

	public function test_loading_empty_bindings(): void {
		$this->initTempPlugin('hook-bindings');
		$a = new DirectoryBasedLoader(new Configuration(['hook_resources_path' => './']), new PhpFileLoader());
		$actual = [];
		foreach ($a->load() as $k => $v) {
			$actual[$k] = array_merge( $actual[$k] ?? [], (array) $v );
		}
		$this->assertEquals(
			[
				'hook1' => ['binding'],
				'plugins_loaded' => ['binding1', 'binding2'],
			],
			$actual
		);
	}

}
