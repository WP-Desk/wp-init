<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use WPDesk\Init\Plugin\DefaultHeaderParser;

class DefaultHeaderParserTest extends TestCase {

	/** @dataProvider provider */
	public function test_should_parse_plugin_data_from_file( $name,  string $content, array $expected ): void {
		$file = $this->createTempFile($name, $content);

		$data = new DefaultHeaderParser();
		$this->assertEquals( $expected, $data->parse( $file ) );
	}

	public function provider(): iterable {
		yield [
			'first.php',
<<<PHP
<?php
/**
 * Plugin Name: Example plugin
 */
PHP,
			[ 'Name' => 'Example plugin' ],
		];

		yield [
			'second.php',
<<<PHP
<?php
/**
 * Plugin Name: ShopMagic for WooCommerce
 * Plugin URI: https://shopmagic.app/
 * Description: Marketing Automation and Custom Email Designer for WooCommerce
 * Version: 3.0.9-beta.1
 * Author: WP Desk
 * Author URI: https://shopmagic.app/
 * Text Domain: shopmagic-for-woocommerce
 * Domain Path: /lang/
 * Requires at least: 5.0
 * Tested up to: 6.1
 * WC requires at least: 4.8
 * WC tested up to: 7.2
 * Requires PHP: 7.2
 */
PHP,
			[
				'Name'        => 'ShopMagic for WooCommerce',
				'PluginURI'   => 'https://shopmagic.app/',
				'Version'     => '3.0.9-beta.1',
				'Author'      => 'WP Desk',
				'AuthorURI'   => 'https://shopmagic.app/',
				'TextDomain'  => 'shopmagic-for-woocommerce',
				'DomainPath'  => '/lang/',
				'RequiresWP'  => '5.0',
				'RequiresWC'  => '4.8',
				'RequiresPHP' => '7.2',
				'TestedWP'    => '6.1',
				'TestedWC'    => '7.2',
],
		];
	}

}
