<?php
declare( strict_types=1 );

namespace WPDesk\Init\Tests;

use WPDesk\Init\Configuration\Configuration;
use WPDesk\Init\Dumper\PhpFileDumper;
use WPDesk\Init\Loader\PhpFileLoader;
use WPDesk\Init\PluginHeaderData;
use WPDesk\Init\DefaultHeaderParser;

class PluginHeaderDataTest extends TestCase {

	public function test_loading_cached_plugin_data() {
		$dir = $this->initTempPlugin();

		$header = new PluginHeaderData(
			new DefaultHeaderParser(),
			new PhpFileLoader(),
			new PhpFileDumper(),
			new Configuration( [] )
		);

		$this->assertFileDoesNotExist( $dir . '/generated/plugin.php' );
		$header->get_plugin_data( $dir . '/simple-plugin.php' );

		$this->assertFileExists( $dir . '/generated/plugin.php' );
		$header->get_plugin_data( $dir . '/simple-plugin.php' );
	}

}