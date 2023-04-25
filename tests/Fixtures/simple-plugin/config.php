<?php

return [
	'bundles'   => [
		new class {
			public static function subscribers(): array {
				return [
					'WPDesk\Init\Tests\Fixtures\SimplePlugin\SimplePluginSubscriber',
				];
			}
		}
	],
	'hookables' => [
		new class implements \WPDesk\Init\HookProvider\HookProvider {
			public function hooks(): void {
				// TODO: Implement hooks() method.
			}
		}
	]
];