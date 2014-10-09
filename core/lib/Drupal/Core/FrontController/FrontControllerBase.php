<?php


namespace Drupal\Core\FrontController;

use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for front controllers.
 */
abstract class FrontControllerBase implements FrontControllerInterface {

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   */
  function __construct(Request $request) {
    $this->request = $request;
  }

} 
