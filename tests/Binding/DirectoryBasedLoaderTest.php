<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Binding;

use WPDesk\Init\Binding\Definition\UnknownDefinition;
use WPDesk\Init\Binding\Loader\DirectoryBasedLoader;
use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\Tests\TestCase;

class DirectoryBasedLoaderTest extends TestCase {

	public function xtest_throws_when_configuration_entry_is_missing(): void {
		$this->expectException(\InvalidArgumentException::class);
		$a = new DirectoryBasedLoader(new Configuration([]));
		$a->load();
	}

	public function test_loading_empty_bindings(): void {
		$this->initTempPlugin('hook-bindings');
		$a = new DirectoryBasedLoader('./');
		$actual = iterator_to_array($a->load(), false);
		$this->assertEquals(
			[
				new UnknownDefinition('binding', 'hook1'),
				new UnknownDefinition('binding1', 'plugins_loaded'),
				new UnknownDefinition('binding2', 'plugins_loaded'),
			],
			$actual
		);
	}

	public function test_load_illogical_bindings(): void {
		$this->initTempPlugin('borked-bindings');
		$a = new DirectoryBasedLoader('./');

		$actual = iterator_to_array($a->load(), false);
		$this->assertEquals(
			[
				new UnknownDefinition('binding', 'hook1'),
				new UnknownDefinition('binding1', 'plugins_loaded'),
				new UnknownDefinition('binding2', 'plugins_loaded'),
			],
			$actual
		);
	}

}
