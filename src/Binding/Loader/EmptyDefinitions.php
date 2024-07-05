<?php
declare( strict_types=1 );

namespace WPDesk\Init\Binding\Loader;

final class EmptyDefinitions implements BindingDefinitions {

	public function load(): iterable {
		return [];
	}
}
