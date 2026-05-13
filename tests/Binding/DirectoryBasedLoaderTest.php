<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Binding;

use WPDesk\Init\Binding\Definition\CallableDefinition;
use WPDesk\Init\Binding\Exception\InvalidBindingDefinition;
use WPDesk\Init\Binding\Loader\FilesystemDefinitions;
use WPDesk\Init\Tests\TestCase;

class DirectoryBasedLoaderTest extends TestCase {

	public function test_throws_when_path_does_not_exist(): void {
		$this->expectException(\InvalidArgumentException::class);
		$a = new FilesystemDefinitions('./missing');
		iterator_to_array($a->load(), false);
	}

	public function test_loading_callable_bindings_from_directory(): void {
		$this->initTempPlugin('hook-bindings');
		$a = new FilesystemDefinitions('./');
		$actual = iterator_to_array($a->load(), false);
		$this->assertCount( 3, $actual );
		$this->assertContainsOnlyInstancesOf( CallableDefinition::class, $actual );
		$this->assertSame( 'hook1', $actual[0]->hook() );
		$this->assertSame( 'plugins_loaded', $actual[1]->hook() );
		$this->assertSame( 'plugins_loaded', $actual[2]->hook() );
	}

	public function test_load_invalid_bindings_throws(): void {
		$this->initTempPlugin('borked-bindings');
		$a = new FilesystemDefinitions('./');

		$this->expectException( InvalidBindingDefinition::class );
		iterator_to_array($a->load(), false);
	}

}
