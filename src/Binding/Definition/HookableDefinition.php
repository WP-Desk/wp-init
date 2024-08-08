<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Definition;

use WPDesk\Init\Binding\Definition;

/** @implements Definition<class-string<Hookable>> */
class HookableDefinition implements Definition {

	/** @var ?string */
	private $hook;

	/** @var class-string<Hookable> */
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
