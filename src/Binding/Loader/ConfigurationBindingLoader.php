<?php
declare( strict_types=1 );

namespace WPDesk\Init\Binding\Loader;

use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\Loader\PhpFileLoader;
use WPDesk\Init\Util\Path;

class ConfigurationBindingLoader extends DirectoryBasedLoader {

	public function __construct(
		ReadableConfig $config,
		string $plugin_path,
		PhpFileLoader $loader,
		DefinitionFactory $def_factory
	) {
		parent::__construct(
			( new Path( $config->get( 'hook_resources_path' ) ) )->absolute( $plugin_path ),
			$loader,
			$def_factory
		);
	}
}
