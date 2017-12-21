<?php


namespace Donquixote\Containerkit\Container;

/**
 * Base class for hard-coded service or value containers.
 *
 * See http://dqxtech.net/blog/2014-06-13/simple-do-it-yourself-php-service-container
 *
 * This one has a __set() method, that allows to set some services/values, so
 * they no longer need to be calculated.
 */
abstract class SettableContainerBase implements SettableContainerInterface {

  use SettableContainerTrait;
}
