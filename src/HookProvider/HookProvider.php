<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookProvider;

interface HookProvider {

	public function hooks(): void;

}