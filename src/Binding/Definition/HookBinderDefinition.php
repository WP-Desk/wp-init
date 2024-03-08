<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Definition;

use WPDesk\Init\Binding\Definition;
use WPDesk\Init\Binding\HookBinder;

/** @implements Definition<class-string<HookBinder>> */
class HookBinderDefinition implements Definition {

	/** @var ?string */
	private $hook;

	/** @var class-string<HookBinder> */
	private $hookable;

	public function __construct(
		string $hookable,
		?string $hook = null,
	) {
		$this->hook     = $hook;
		$this->hookable = $hookable;
	}

	public function hook(): ?string {
		return $this->hook;
	}

	public function value() {
		return $this->hookable;
	}
}
