<?php


namespace Drupal\Core\FrontController;

/**
 * Interface for front controllers.
 *
 * Front controllers are typically created in @see CoreServices. They typically
 * depend on the request object and the site object, which they receive through
 * the constructor.
 */
interface FrontControllerInterface {

  /**
   * Executes the front controller operation.
   */
  function sendResponse();
} 
