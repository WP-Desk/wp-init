<?php
declare( strict_types=1 );

namespace WPDesk\Init\Binding\Loader;

use WPDesk\Init\Binding\Definition;

final class EmptyDefinitions implements BindingDefinitions {

	/** @return iterable<Definition<mixed>> */
	public function load(): iterable {
		return [];
	}
}
