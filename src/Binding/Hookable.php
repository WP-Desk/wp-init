<?php
// phpcs:disable

declare(strict_types=1);

namespace WPDesk\Init\Binding;

/**
 * Operate in mode compatible with wp-builder library, as wp-init roots on it and needs to preserve the easy way to upgrade existing clients.
 */
if ( class_exists( \WPDesk\PluginBuilder\Plugin\Hookable::class ) ) {
	interface Hookable extends \WPDesk\PluginBuilder\Plugin\Hookable {
		public function hooks(): void;
	}
} else {
	interface Hookable {
		public function hooks(): void;
	}
}
