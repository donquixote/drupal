<?php


namespace Drupal\Component\MiniContainer;

/**
 * Base class for a light-weight container.
 *
 * Container classes extending this e.g. for a service container should define
 * methods to create specific services, and add '@property' type hints for every
 * service.
 */
abstract class MiniContainerBase {

  /**
   * @var mixed[]
   */
  private $cache = array();

  /**
   * Explicitly initializes a value, before it is being lazy-initialized.
   *
   * @param string $key
   * @param mixed $value
   *
   * @throws \Drupal\Component\MiniContainer\MiniContainerException
   */
  public function __set($key, $value) {
    if (array_key_exists($key, $this->cache)) {
      throw new MiniContainerException(sprintf('Key "%s" is already initialized.', $key));
    }
    // Validation callbacks may throw exceptions.
    $this->validate($key, $value);
    $this->cache[$key] = $value;
  }

  /**
   * Validates a value before it is being set.
   *
   * @param string $key
   * @param mixed $value
   *
   * @throws \Drupal\Component\MiniContainer\MiniContainerException
   */
  protected function validate($key, $value) {
    $factory_method = 'get_' . $key;
    if (!method_exists($this, $factory_method)) {
      // Keys are only 'known' if there is a get_ method.
      throw new MiniContainerException(sprintf('Unknown key "%s"', $key));
    }
    $validate_method = 'validate_' . $key;
    if (!method_exists($this, $validate_method)) {
      // Keys can only be initialized if there is a validate method.
      throw new MiniContainerException(sprintf('Key "%s" is known, but has no validate method.', $key));
    }
    $this->$validate_method($value);
  }

  /**
   * Obtains a lazy-created service.
   *
   * Magic __get() is used to allow type hints with '@property'.
   *
   * @param string $key
   *   Name of the service, to be transformed into a method name.
   *
   * @return mixed
   *
   * @throws \Drupal\Component\MiniContainer\MiniContainerException
   */
  public function __get($key) {
    return array_key_exists($key, $this->cache)
      ? $this->cache[$key]
      : $this->cache[$key] = $this->valueForKey($key);
  }

  /**
   * Determines a value for the given key.
   *
   * @param string $key
   *
   * @return mixed
   *
   * @throws \Drupal\Component\MiniContainer\MiniContainerException
   */
  protected function valueForKey($key) {
    // Attempt to create the non-existing service.
    $method = 'get_' . $key;
    if (!method_exists($this, $method)) {
      // Keys are only 'known' if there is a get_ method.
      throw new MiniContainerException(sprintf('Unknown key "%s".', $key));
    }
    return $this->$method();
  }

}
