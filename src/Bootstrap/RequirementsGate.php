<?php
declare( strict_types=1 );

namespace WPDesk\Init\Bootstrap;

use WPDesk\Init\Plugin\Plugin;

final class RequirementsGate implements BootGate {

	private \WPDesk_Requirement_Checker $checker;

	/**
	 * @param array<string, mixed> $requirements
	 */
	public function __construct( Plugin $plugin, array $requirements ) {
		$this->checker = ( new \WPDesk_Basic_Requirement_Checker_Factory() )->create_from_requirement_array(
			$plugin->get_basename(),
			$plugin->get_name(),
			$requirements,
			$plugin->get_slug()
		);
	}

	public function can_boot(): bool {
		return $this->checker->are_requirements_met();
	}

	public function on_failure(): void {
		$this->checker->render_notices();
	}
}
