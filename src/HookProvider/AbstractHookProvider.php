<?php
declare( strict_types=1 );

namespace WPDesk\Init\HookProvider;

use WPDesk\Init\HooksProvider;
use WPDesk\Init\PluginAwareInterface;
use WPDesk\Init\PluginAwareTrait;

abstract class AbstractHookProvider implements HooksProvider, PluginAwareInterface {
	use PluginAwareTrait;

}