<?php
declare( strict_types=1 );

namespace WPDesk\Init\Loader;

class PhpFileLoader {

	/**
	 * @param string|Path $resource
	 *
	 * @return mixed
	 */
	public function load( $resource ) {
		// TODO: add file locator
		return ( static function () use ( $resource ) {
			if ( ! is_readable( (string) $resource ) ) {
				throw new \RuntimeException( "Could not load $resource" );
			}

			$data = include (string) $resource;
			if ( $data === false ) {
				throw new \RuntimeException( "Could not load $resource" );
			}

			return $data;
		} )();
	}
}
