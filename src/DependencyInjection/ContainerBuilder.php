<?php
declare( strict_types=1 );

namespace WPDesk\Init\DependencyInjection;

use DI\Container;
use DI\ContainerBuilder as DiBuilder;
use DI\Definition\Source\DefinitionSource;

final class ContainerBuilder {

	/** @var DiBuilder */
	private $original_builder;

	public function __construct( DiBuilder $original_builder ) {
		$this->original_builder = $original_builder;
	}

	/**
	 * Add definitions to the container.
	 *
	 * @param string|array|DefinitionSource ...$definitions
	 *  Can be an array of definitions, the name of a file containing definitions or
	 *  a DefinitionSource object.
	 *
	 * @return $this
	 */
	public function add_definitions( ...$definitions ): self {
		$this->original_builder->addDefinitions( ...$definitions );

		return $this;
	}

	public function build(): Container {
		return $this->original_builder->build();
	}

}
