<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding;

interface Hookable extends \WPDesk\PluginBuilder\Plugin\Hookable {

	public function hooks(): void;
}
