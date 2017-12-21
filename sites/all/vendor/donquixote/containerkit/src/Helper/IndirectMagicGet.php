<?php

namespace Donquixote\Containerkit\Helper;

use Donquixote\Containerkit\Container\ContainerInterface;

/**
 * A trick object to circumvent a limitation of PHP with __get().
 *
 * @see \Drupal\containerkit\Container\StubbableContainerBase
 *
 * @property ..,
 *   Supports all the magic properties of the container it is decorating.
 */
class IndirectMagicGet {

  /**
   * @var \Donquixote\Containerkit\Container\ContainerInterface
   */
  private $container;

  /**
   * @param \Donquixote\Containerkit\Container\ContainerInterface $container
   */
  function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * @param $key
   *
   * @return mixed
   */
  function __get($key) {
    return $this->container->__get($key);
  }

}
