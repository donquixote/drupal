<?php


namespace Drupal\Component\LightContainer;

/**
 * A class that can manage phases.
 *
 * Phases can only begin once and never end during a request, but one phase can
 * depend on another.
 */
abstract class AbstractLightPhaseContainer {

  /**
   * @var true[]
   */
  protected $phases = array();

  /**
   * Begins a phase.
   *
   * Magic __get() is used to allow type hints with '@property'.
   *
   * @param string $key
   *   Name of the service, to be transformed into a method name.
   *
   * @return true
   * @throws \Exception
   */
  public function __get($key) {
    if (array_key_exists($key, $this->phases)) {
      return TRUE;
    }
    // Attempt to create the non-existing service.
    $method = 'init' . $key;
    if (method_exists($this, $method)) {
      $this->$method();
      $this->phases[$key] = TRUE;
      return TRUE;
    }
    throw new \Exception("Unknown phase '$key'.");
  }
} 
