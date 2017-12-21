<?php


namespace Donquixote\Containerkit\Container;

use Donquixote\Containerkit\Exception\ContainerException;
use Donquixote\Containerkit\Helper\IndirectMagicGet;

/**
 * Base class for hard-coded service containers.
 *
 * This version has a special way to deal with circularity.
 */
abstract class StubbableContainerBase implements SettableContainerInterface {

  use SettableContainerTrait;

  /**
   * @param string $key
   *
   * @return mixed
   */
  function __get($key) {
    if (array_key_exists($key, $this->buffer)) {
      return $this->buffer[$key];
    }
    $method = 'get_' . $key;
    if (!method_exists($this, $method)) {
      // @todo Sanitize $key for exception message?
      throw new ContainerException("Service or value for '$key' already set.");
    }
    $value = $this->$method();
    if (array_key_exists($key, $this->buffer)) {
      // A recursive call to $this->$method() has already filled the key.
      return $this->buffer[$key];
    }
    return $this->buffer[$key] = $value;
  }

  /**
   * Returns a trick object to circumvent a limitation in PHP for __get().
   *
   * @return static|\Donquixote\Containerkit\Helper\IndirectMagicGet
   *   Actually, an IndirectMagicGet object.
   *   But we make the IDE believe that it returns an object of the same type
   *   as the container. This allows the IDE to recognize all the @property
   *   hints on the actual container.
   */
  protected function circular() {
    return new IndirectMagicGet($this);
  }
}
