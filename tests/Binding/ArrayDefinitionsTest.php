<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Binding;

use WPDesk\Init\Binding\Definition\UnknownDefinition;
use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Tests\TestCase;

class ArrayDefinitionsTest extends TestCase {

	public function test_loading_empty_bindings(): void {
		$a = new ArrayDefinitions([]);
		$this->assertEquals(0, iterator_count($a->load()));
	}

	public function test_loading_structured_bindings(): void {
		$a = new ArrayDefinitions([
			'hook' => [
				'bind1',
				'bind2',
			],
			'hook2' => [
				'bind3',
			]
		]);
		$this->assertEquals(
			[
				new UnknownDefinition('bind1', 'hook'),
				new UnknownDefinition('bind2', 'hook'),
				new UnknownDefinition('bind3', 'hook2'),
			],
			iterator_to_array($a->load())
		);
	}

	public function test_loading_unstructured_bindings(): void {
		$a = new ArrayDefinitions([
			'bind1',
			'bind2',
			'hook' => 'bind3',
		]);
		$this->assertEquals(
			[
				new UnknownDefinition('bind1', null),
				new UnknownDefinition('bind2', null),
				new UnknownDefinition('bind3', 'hook'),
			],
			iterator_to_array($a->load())
		);

		$a = new ArrayDefinitions([
			'bind1',
			'not_a_hook' => 'bind2',
			'hook' => ['bind3'],
		]);
		$this->assertEquals(
			[
				new UnknownDefinition('bind1', null),
				new UnknownDefinition('bind2', 'not_a_hook'),
				new UnknownDefinition('bind3', 'hook'),
			],
			iterator_to_array($a->load())
		);
	}

}
