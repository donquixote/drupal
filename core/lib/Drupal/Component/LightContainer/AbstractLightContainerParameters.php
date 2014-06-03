<?php


namespace Drupal\Component\LightContainer;

/**
 * Base class for container parameters.
 *
 * Allows setting of parameter values, but freezes the parameter once it is
 * first used.
 */
abstract class AbstractLightContainerParameters {

  /**
   * Default parameter values.
   *
   * @var mixed[]
   */
  protected $defaults = array();

  /**
   * @var mixed[]
   */
  protected $parameters = array();

  /**
   * @param mixed[] $defaults
   */
  public function __construct(array $defaults = array()) {
    $this->defaults = $defaults;
  }

  /**
   * @param $key
   * @param $value
   *
   * @throws \Exception
   */
  public function __set($key, $value) {
    if (array_key_exists($key, $this->parameters)) {
      throw new \Exception("Parameter '$key' already initialized.");
    }
    $this->defaults[$key] = $value;
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
   * @throws \Exception
   */
  public function __get($key) {
    if (array_key_exists($key, $this->parameters)) {
      return $this->parameters[$key];
    }
    // Attempt to create the non-existing parameter.
    if (array_key_exists($key, $this->defaults)) {
      return $this->parameters[$key] = $this->defaults[$key];
    }
    $method = 'get' . $key;
    if (method_exists($this, $method)) {
      return $this->parameters[$key] = $this->$method();
    }
    throw new \Exception("Unknown parameter name '$key'.");
  }

} 
