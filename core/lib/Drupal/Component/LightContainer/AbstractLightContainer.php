<?php


namespace Drupal\Component\LightContainer;

/**
 * Base class for a light-weight container.
 *
 * Container classes extending this should define methods to create specific
 * services, and add '@property' type hints for every service.
 */
abstract class AbstractLightContainer {

  /**
   * @var mixed[]
   */
  protected $services = array();

  /**
   * Explicitly set a service, assuming it has not been lazy-initialized yet.
   *
   * @param string $key
   * @param mixed $service
   *
   * @throws \Exception
   */
  public function initService($key, $service) {
    if (array_key_exists($key, $this->services)) {
      throw new \Exception("Service '$key' already initialized.");
    }
    $this->services[$key] = $service;
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
    if (array_key_exists($key, $this->services)) {
      return $this->services[$key];
    }
    // Attempt to create the non-existing service.
    $method = 'get' . $key;
    if (method_exists($this, $method)) {
      return $this->services[$key] = $this->$method();
    }
    throw new \Exception("Unknown service '$key'.");
  }

} 
