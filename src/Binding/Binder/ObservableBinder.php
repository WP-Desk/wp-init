<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Binder;

use WPDesk\Init\Binding\Binder;
use WPDesk\Init\Binding\ComposableBinder;
use WPDesk\Init\Binding\Definition;

/**
 * Binder decorator, specifically built for testing purposes. Can naively investigate other binders.
 */
final class ObservableBinder implements ComposableBinder {

	/** @var Binder */
	private $binder;

	private $binds_count = 0;

	public function __construct( Binder $b ) {
		$this->binder = $b;
	}

	public function bind( Definition $def ): void {
		$this->binder->bind( $def );
		++$this->binds_count;
	}

	public function can_bind( Definition $def ): bool {
		if ( $this->binder instanceof ComposableBinder ) {
			return $this->binder->can_bind( $def );
		}

		return true;
	}

	public function binds_count(): int {
		return $this->binds_count;
	}
}
