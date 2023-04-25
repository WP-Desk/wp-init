<?php

namespace WPDesk\Init;

interface HeaderParser {

	public function parse( string $plugin_file ): array;
}