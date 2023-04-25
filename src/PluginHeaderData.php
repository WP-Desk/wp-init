<?php
declare( strict_types=1 );

namespace WPDesk\Init;

use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\Dumper\PhpFileDumper;
use WPDesk\Init\Loader\PhpFileLoader;

class PluginHeaderData {

	/** @var PluginHeaderParser */
	private $parser;

	/** @var PhpFileLoader */
	private $loader;

	/** @var PhpFileDumper */
	private $dumper;

	/** @var ReadableConfig */
	private $config;

	public function __construct(
		PluginHeaderParser $parser,
		PhpFileLoader $loader,
		PhpFileDumper $dumper,
		ReadableConfig $config
	) {
		$this->parser = $parser;
		$this->loader = $loader;
		$this->dumper = $dumper;
		$this->config = $config;
	}

	public function get_plugin_data( string $plugin_file ): array {
		$cache_path = $this->config->get( 'cache_path', 'generated' ) . '/plugin.php';
		try {
			return $this->loader->load( $cache_path );
		} catch ( \Exception $e ) {
			$plugin_data = $this->parser->parse( $plugin_file );
			$this->dumper->dump( $plugin_data, $cache_path );

			return $this->loader->load( $cache_path );
		}
	}

}