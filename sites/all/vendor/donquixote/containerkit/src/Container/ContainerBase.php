<?php

namespace Donquixote\Containerkit\Container;

/**
 * Base class for hard-coded service containers.
 *
 * See http://dqxtech.net/blog/2014-06-13/simple-do-it-yourself-php-service-container
 *
 * This is a basic version, with only a __get() method and nothing more.
 */
class ContainerBase implements ContainerInterface {

  use ContainerTrait;

}
