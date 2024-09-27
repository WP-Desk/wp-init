<?php

declare(strict_types=1);

namespace WPDesk\Init\Binding\Definition;

use WPDesk\Init\Binding\Definition;

/** @implements Definition<mixed> */
class DefinitionCollection implements Definition {

	/** @var ?string */
	private $hook;

	/** @var Definition[] */
	private $defs;

	/** @var array<string, mixed> */
	private array $options;

	public function __construct(
		?string $hook = null,
		array $options = []
	) {
		$this->hook    = $hook;
		$this->options = $options;
	}

	public function hook(): ?string {
		return $this->hook;
	}

	public function value() {
		yield from $this->defs;
	}

	public function add( Definition $def ) {
		$this->defs[] = $def;
	}

	public function option( string $name ) {
		return $this->options[ $name ] ?? null;
	}
}
