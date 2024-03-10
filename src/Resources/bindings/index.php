<?php

use WPDesk\Init\Extension\CommonBinding\I18n;
use WPDesk\Init\Extension\CommonBinding\RequirementsCheck;
use WPDesk\Init\Extension\CommonBinding\CustomOrderTableCompatibility;

return [
	RequirementsCheck::class,
	I18n::class,
	CustomOrderTableCompatibility::class,
];
