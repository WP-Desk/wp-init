<?php
declare(strict_types=1);

namespace WPDesk\Init\Binding;

final class ObservableBinder implements HookBinder {

	/** @var HookBinder */
	private $binder;

	private $is_bound = false;

	public function __construct( HookBinder $binder ) {
		$this->binder = $binder;
	}

	public function bind(): void {
		$this->binder->bind();
		$this->is_bound = true;
	}

	public function is_bound(): bool {
		return $this->is_bound;
	}
}
