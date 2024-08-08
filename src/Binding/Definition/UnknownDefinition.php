<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Definition;

use WPDesk\Init\Binding\Definition;

/** @implements Definition<mixed> */
class UnknownDefinition implements Definition {

	/** @var ?string */
	private $hook;

	/** @var mixed */
	private $value;

	public function __construct(
		$value,
		?string $hook = null
	) {
		$this->value = $value;
		$this->hook     = $hook;
	}

	public function hook(): ?string {
		return $this->hook;
	}

	public function value() {
		return $this->value;
	}
}
