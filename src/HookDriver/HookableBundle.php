<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookDriver;

interface HookableBundle extends \WPDesk\Init\Bundle\Bundle {

	public static function hookable(): array;

}