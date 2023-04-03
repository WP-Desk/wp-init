<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use WPDesk\Init\HookProvider\Conditional;
use WPDesk\Init\HookProvider\HooksProvider;
use WPDesk\Init\Plugin;

class PluginTest extends \PHPUnit\Framework\TestCase {

  public function test_should_register_deferred_hook_provider(): void {
    $plugin = new Plugin();
    $provider = new class implements DeferredHooksProvider {
      public $called = false;

      public function register_hooks(): void {
        $this->called = true;
      }
      public function register_after(): string {
        return 'admin_init';
      }
    };

    $plugin->register_hooks( $provider );
  }

	public function test_should_register_hook_provider(): void {
		$plugin = new Plugin();

		$provider = new class implements HooksProvider {
			public $called = false;

			public function register_hooks(): void {
				$this->called = true;
			}
		};

		$plugin->register_hooks( $provider );

		$this->assertTrue( $provider->called );
	}

	public function test_should_not_register_failing_conditional_hook_provider(): void {
		$plugin = new Plugin();

		$provider = new class implements HooksProvider, Conditional {
			public $called = false;

			public function is_needed(): bool {
				return false;
			}

			public function register_hooks(): void {
				$this->called = true;
			}
		};

		$plugin->register_hooks( $provider );

		$this->assertFalse( $provider->called );
	}

}
