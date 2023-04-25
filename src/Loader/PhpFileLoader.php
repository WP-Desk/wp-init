<?php
declare( strict_types=1 );

namespace WPDesk\Init\Loader;

class PhpFileLoader {

	/**
	 * @param string $resource
	 *
	 * @return mixed
	 */
	public function load( string $resource ) {
		// TODO: add file locator
		return ( static function () use ( $resource ) {
			return include $resource;
		} )();
	}

}