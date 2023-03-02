<?php

declare(strict_types=1);

namespace WPDesk\Init;

use Psr\Container\ContainerInterface;

trait ContainerAwareTrait {

  /** @var ContainerInterface */
  private $container;

  public function set_container( ContainerInterface $container ): void {
    $this->container = $container;
  }
}
