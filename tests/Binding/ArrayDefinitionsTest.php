<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Binding;

use WPDesk\Init\Binding\Loader\ArrayDefinitions;
use WPDesk\Init\Binding\Exception\InvalidBindingDefinition;
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

		$this->expectException( InvalidBindingDefinition::class );
		iterator_to_array($a->load());
	}

	public function test_loading_unstructured_bindings(): void {
		$a = new ArrayDefinitions([
			'bind1',
			'bind2',
			'hook' => 'bind3',
		]);
		$this->expectException( InvalidBindingDefinition::class );
		iterator_to_array($a->load());
	}

	public function test_loading_invalid_hook_definitions_throws(): void {
		$a = new ArrayDefinitions([
			'bind1',
			'not_a_hook' => 'bind2',
			'hook' => ['bind3'],
		]);

		$this->expectException( InvalidBindingDefinition::class );
		iterator_to_array($a->load());
	}

	public function test_invalid_existing_class_string_error_explains_missing_hookable_contract(): void {
		$a = new ArrayDefinitions([
			'plugins_loaded' => NonHookableBindingDefinitionFixture::class,
		]);

		$this->expectException( InvalidBindingDefinition::class );
		$this->expectExceptionMessage(
			'class-string "' . NonHookableBindingDefinitionFixture::class . '", but it does not implement WPDesk\Init\Binding\Hookable'
		);
		iterator_to_array($a->load());
	}

	public function test_invalid_missing_class_string_error_includes_value(): void {
		$a = new ArrayDefinitions([
			'plugins_loaded' => 'Vendor\Plugin\MissingHookProvider',
		]);

		$this->expectException( InvalidBindingDefinition::class );
		$this->expectExceptionMessage(
			'string "Vendor\Plugin\MissingHookProvider", which is not an autoloadable hookable class or callable'
		);
		iterator_to_array($a->load());
	}

}

class NonHookableBindingDefinitionFixture {}
