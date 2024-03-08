<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Binder;

use WPDesk\Init\Binding\Binder;
use WPDesk\Init\Binding\Definition;
use WPDesk\Init\Binding\Definition\HookableDefinition;

final class CompositeBinder implements Binder {

	/** @var Binder[] */
	private $binders;

	public function __construct( Binder ...$binders ) {
		$this->binders = $binders;
	}

	public function add( Binder $binder ): void {
		$this->binders[] = $binder;
	}

	public function can_bind( Definition $def ): bool {
		foreach ( $this->binders as $binder ) {
			if ( $binder->can_bind( $def ) ) {
				return true;
			}
		}

		return false;
	}

	public function bind( Definition $def ): void {
		foreach ( $this->binders as $binder ) {
			if ( $binder->can_bind( $def ) ) {
				$binder->bind( $def );
				break;
			}
		}
	}
}
