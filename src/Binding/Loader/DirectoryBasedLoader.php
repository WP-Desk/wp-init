<?php
declare( strict_types=1 );

namespace WPDesk\Init\Binding\Loader;

use WPDesk\Init\Binding\DefinitionFactory;
use WPDesk\Init\Plugin\Plugin;
use WPDesk\Init\Util\Path;
use WPDesk\Init\Configuration\ReadableConfig;
use WPDesk\Init\Loader\PhpFileLoader;

class DirectoryBasedLoader implements BindingDefinitions {

	/** @var Path */
	private $path;

	/** @var PhpFileLoader */
	private $loader;

	/** @var DefinitionFactory */
	private $def_factory;

	public function __construct( $path, PhpFileLoader $loader, DefinitionFactory $def_factory ) {
		$this->path        = new Path( (string) $path );
		$this->loader      = $loader;
		$this->def_factory = $def_factory;
	}

	public function load(): iterable {
		if ( $this->path->is_directory() ) {
			foreach ( $this->path->read_directory() as $filename ) {
				yield from $this->load_from_file( $filename );
			}
		} else {
			yield from $this->load_from_file( $this->path );
		}
	}

	private function load_from_file( Path $filename ) {
		if ( ! $filename->is_file() ) {
			return;
		}

		$hooks = $this->loader->load( (string) $filename );

		if ( $filename->get_basename() !== 'index.php' ) {
			$hooks = [ $filename->get_filename_without_extension() => $hooks ];
		}

		yield from (new ArrayBindingLoader( $hooks, $this->def_factory ))->load();
	}
}
