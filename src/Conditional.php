<?php

declare( strict_types=1 );

namespace WPDesk\Init;

/**
 * Something that can be instantiated conditionally.
 *
 * In hook provider context a class marked as being conditional can be asked whether its
 * hooks should be fired and integrated into WordPress system. An example would be a service that is
 * only available on the admin backend.
 *
 * This allows for a more systematic and automated optimization of how the
 * different parts of the plugin are enabled or disabled.
 *
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 */
interface Conditional {

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public function is_needed(): bool;
}
