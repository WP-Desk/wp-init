<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding;

interface Binder {

	/** @param Definition<mixed> $def */
	public function bind( Definition $def ): void;
}
