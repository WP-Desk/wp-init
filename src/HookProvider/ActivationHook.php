<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookProvider;

abstract class ActivationHook extends AbstractHookProvider {

	public function register_hooks(): void {
		register_activation_hook(
			$this->plugin->get_basename(),
			[$this, 'activate']
		);
	}

	abstract public function activate(): void;
}