<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use WPDesk\Init\PluginHeaderParser;

class PluginHeaderParserTest extends TestCase {

	public function test_should_parse_plugin_data(): void {
		$data = new PluginHeaderParser();
		$dir  = $this->initTempPlugin();

		$result = $data->get_plugin_data( $dir . '/simple-plugin.php' );

		$this->assertEquals( [ 'Name' => 'Example plugin' ], $result );
	}

	public function test_should_parse_whole_plugin_data(): void {
		$data = new PluginHeaderParser();
		$dir  = $this->initTempPlugin( 'advanced-plugin' );

		$result = $data->get_plugin_data( $dir . '/advanced-plugin.php' );

		$this->assertEquals(
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
			$result
		);
	}

}