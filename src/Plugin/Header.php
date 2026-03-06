<?php

declare(strict_types=1);

namespace WPDesk\Init\Plugin;

final class Header {

	private array $header_data;

	public function __construct( array $header_data ) {
		$this->header_data = $header_data;
	}

	public function get( string $key ) {
		return $this->header_data[ $key ];
	}

	public function has( string $key ): bool {
		return isset( $this->header_data[ $key ] );
	}
}
