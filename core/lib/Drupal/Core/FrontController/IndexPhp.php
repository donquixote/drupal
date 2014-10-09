<?php


namespace Drupal\Core\FrontController;

use Drupal\Core\DrupalKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * Front controller for the index.php in case of a web request on an installed
 * site.
 *
 * @see CoreServices::IndexPhp
 */
class IndexPhp extends FrontControllerBase {

  /**
   * @var \Drupal\Core\DrupalKernelInterface
   */
  protected $kernel;

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\Core\DrupalKernelInterface $kernel
   */
  function __construct(Request $request, DrupalKernelInterface $kernel) {
    parent::__construct($request);
    $this->kernel = $kernel;
  }

  /**
   * Executes the front controller operation.
   */
  function sendResponse() {

    $request = $this->request;
    $kernel = $this->kernel;

    $response = $kernel->handle($request);
    $response->prepare($request);
    $response->send();

    // Terminate.
    if ($kernel instanceof TerminableInterface) {
      $kernel->terminate($request, $response);
    }
  }
}
