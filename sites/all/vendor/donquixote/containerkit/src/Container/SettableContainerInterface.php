<?php

namespace Donquixote\Containerkit\Container;

interface SettableContainerInterface extends ContainerInterface {

  /**
   * @param string $key
   * @param mixed $value
   *
   * @throws \Donquixote\Containerkit\Exception\ContainerException
   */
  function __set($key, $value);

}
