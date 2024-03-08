<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests\Binding;

use WPDesk\Init\Binding\Loader\ArrayBindingLoader;
use WPDesk\Init\Binding\Loader\CompositeBindingLoader;
use WPDesk\Init\Tests\TestCase;

class CompositeBindingLoaderTest extends TestCase {

	public function test_loading_empty_bindings(): void {
		$a = new CompositeBindingLoader(new ArrayBindingLoader([]));
		$this->assertEquals(0, iterator_count($a->load()));
	}

	public function test_loading_structured_bindings(): void {
		$a = new CompositeBindingLoader(
			new ArrayBindingLoader(
				[
					'hook' => [
						'bind1',
						'bind2',
					],
				]
			),
			new ArrayBindingLoader(
				[
					'hook2' => [
						'bind3',
					]
				]
			)
		);
		$this->assertEquals(
			[
				'hook' => [
					'bind1',
					'bind2',
				],
				'hook2' => [
					'bind3',
				]
			],
			iterator_to_array($a->load())
		);
	}

	public function test_loading_unstructured_bindings(): void {
		$a = new CompositeBindingLoader(
			new ArrayBindingLoader(	[
				'bind1',
			]),
			new ArrayBindingLoader([
				'bind2',
			]),
			new ArrayBindingLoader([
				'hook' => 'bind3',
			])
		);
		$actual = [];
		foreach ($a->load() as $k => $v) {
			$actual[$k] = array_merge( $actual[$k] ?? [], (array) $v );
		}
		$this->assertEquals(
			[
				'' => ['bind1', 'bind2'],
				'hook' => ['bind3'],
			],
			$actual
		);

		$a = new CompositeBindingLoader(
			new ArrayBindingLoader([
				'bind1',
			]),
			new ArrayBindingLoader([
			'not_a_hook' => 'bind2',
			]),
			new ArrayBindingLoader([
			'hook' => ['bind3'],
			]),
		);
		$actual = [];
		foreach ($a->load() as $k => $v) {
			$actual[$k] = array_merge( $actual[$k] ?? [], (array) $v );
		}
		$this->assertEquals(
			[
				'' => ['bind1'],
				'not_a_hook' => ['bind2'],
				'hook' => ['bind3'],
			],
			$actual
		);
	}
}
