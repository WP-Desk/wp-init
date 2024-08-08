<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding;

interface Binder {

	public function bind( Definition $def ): void;
}
