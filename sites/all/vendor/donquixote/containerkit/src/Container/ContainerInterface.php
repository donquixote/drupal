<?php

namespace Donquixote\Containerkit\Container;

interface ContainerInterface {

  /**
   * @param string $key
   *
   * @return mixed
   */
  function __get($key);

}
