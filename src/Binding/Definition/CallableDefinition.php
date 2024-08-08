<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Definition;

use WPDesk\Init\Binding\Definition;

/** @implements Definition<callable> */
class CallableDefinition implements Definition {

	/** @var ?string */
	private $hook;

	/** @var callable */
	private $callable;

	public function __construct(
		callable $callable,
		?string $hook = null,
	) {
		$this->callable = $callable;
		$this->hook     = $hook;
	}

	public function hook(): ?string {
		return $this->hook;
	}

	public function value() {
		return $this->callable;
	}
}
