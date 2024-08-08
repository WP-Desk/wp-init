<?php
declare(strict_types=1);

namespace WPDesk\Init\Binding;

interface StoppableBinder extends Hookable {

	public function should_stop(): bool;
}
