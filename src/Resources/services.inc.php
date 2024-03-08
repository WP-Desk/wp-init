<?php

use DI\Definition\Helper\AutowireDefinitionHelper;
use WPDesk\Init\CommonBinding\I18n;
use WPDesk\Init\CommonBinding\RequirementsCheck;
/**
 * Define useful bindings for WordPress context.
 */

return [
	wpdb::class => static function () {
		global $wpdb;

		return $wpdb;
	},

	I18n::class => new AutowireDefinitionHelper(),
	RequirementsCheck::class => new AutowireDefinitionHelper(),
];
