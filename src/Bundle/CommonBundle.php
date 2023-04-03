<?php
declare( strict_types=1 );

namespace WPDesk\Init\Bundle;

use WPDesk\Init\HookProvider\ConcreteProviders\ActivationDate;
use WPDesk\Init\HookProvider\ConcreteProviders\I18n;
use WPDesk\Init\HookProvider\ConcreteProviders\WooCommerceHPOSCompatibility;

class CommonBundle {

	public static function subscribers(): iterable {
		return [
			ActivationDate::class,
			I18n::class,
			WooCommerceHPOSCompatibility::class
		];
	}

}