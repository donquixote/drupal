<?php


namespace Drupal\Core\FrontController;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Front controller that redirects e.g. to the install.php
 */
class RedirectFrontController extends FrontControllerBase {

  /**
   * @var string
   *   E.g. 'core/install.php'
   */
  private $redirectPath;

  /**
   * Constructs a RedirectFrontController object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param string $redirect_path
   *   E.g. 'core/install.php'
   */
  function __construct(Request $request, $redirect_path) {
    parent::__construct($request);
    $this->redirectPath = $redirect_path;
  }

  /**
   * Executes the front controller operation.
   */
  function sendResponse() {
    $response = new RedirectResponse($this->request->getBasePath() . '/' . $this->redirectPath);
    $response->prepare($this->request)->send();
  }
}
