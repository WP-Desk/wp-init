<?php

declare(strict_types=1);

namespace WPDesk\Init;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface {

  public function set_container( ContainerInterface $container ): void;

}
