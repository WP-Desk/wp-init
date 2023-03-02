<?php

$plugin = ( new \WPDesk\Init\PluginInit() )
	->set_requirements( [
		'wp'  => '5.6',
		'php' => '7.2'
	] )
	->add_container_definitions( [
		'hello' => 'world',
		'hooks' => [
			'anonymous' => static function () {
				return new class implements \WPDesk\Init\HooksProvider {

					public function register_hooks(): void {
						// TODO: Implement register_hooks() method.
					}
				};
			}
		],
	] )
	->init();
