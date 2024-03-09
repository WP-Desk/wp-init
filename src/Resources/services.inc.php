<?php

use DI\Definition\Helper\AutowireDefinitionHelper;
use WPDesk\Init\Extension\CommonBinding\I18n;
use WPDesk\Init\Extension\CommonBinding\RequirementsCheck;

return [
	wpdb::class              => static function () {
		global $wpdb;

		return $wpdb;
	},

	I18n::class              => new AutowireDefinitionHelper(),
	RequirementsCheck::class => new AutowireDefinitionHelper(),
];
