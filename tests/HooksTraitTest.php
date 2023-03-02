<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

class HooksTraitTest extends \PHPUnit\Framework\TestCase {

	/**
	 * How you can call a hook:
	 * 1. callable string: '__return_true'
	 * 2. closure: function () { echo "hello"; }
	 * 3. static call: [stdClass::class, 'hello']
	 * 4. class instance call: [ $this, 'hello' ]
	 * 5. invokable object: $this
	 * ! Since PHP 8.1
	 * 6. first-class callable: __return_true(...), $this->hello(...)
	 */

}