<?php
declare(strict_types=1);

namespace WPDesk\Init\Binding;

interface StoppableBinder extends HookBinder {

	public function should_stop(): bool;
}
