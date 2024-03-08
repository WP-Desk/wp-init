<?php
declare( strict_types=1 );

namespace WPDesk\Init\Binding\Loader;

use WPDesk\Init\Binding\Definition;

interface BindingDefinitions {

	/**
	 * @return iterable<Definition>
	 */
	public function load(): iterable;
}
