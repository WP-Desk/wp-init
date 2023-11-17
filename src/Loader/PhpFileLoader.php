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
			$data = include $resource;
			if ( $data === false ) {
				throw new \RuntimeException( "Could not load $resource" );
			}

			return $data;
		} )();
	}

}
