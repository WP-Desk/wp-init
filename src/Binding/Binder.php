<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding;

interface Binder {

	public function can_bind( Definition $def ): bool;

	public function bind( Definition $def ): void;
}
