<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookProvider;

abstract class DeactivationHook extends AbstractHookProvider {

	public function register_hooks(): void {
		register_deactivation_hook(
			$this->plugin->get_basename(),
			[$this, 'deactivate']
		);
	}

	abstract public function deactivate(): void;
}