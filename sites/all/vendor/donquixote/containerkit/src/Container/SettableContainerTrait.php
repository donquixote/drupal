<?php

namespace Donquixote\Containerkit\Container;

use Donquixote\Containerkit\Exception\ContainerException;

/**
 * The traits allow to make the private $buffer variable available to all
 * the container base classes, but hide it from the actual implementations.
 */
trait SettableContainerTrait {

  use ContainerTrait;

  /**
   * @param string $key
   * @param object $value
   *
   * @throws \Donquixote\Containerkit\Exception\ContainerException
   */
  function __set($key, $value) {
    if (array_key_exists($key, $this->buffer)) {
      // @todo Sanitize $key for exception message?
      throw new ContainerException("Service or value for '$key' already set.");
    }
    $this->buffer[$key] = $value;
  }

}
