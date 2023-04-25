<?php

return [
	wpdb::class => static function () {
		global $wpdb;

		return $wpdb;
	},
];
