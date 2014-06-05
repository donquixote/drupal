<?php


namespace Drupal\Core;


use Drupal\Core\CoreContainer\CoreServices;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 *
 */
class CoreRequestHandler {

  /**
   * @var Request
   */
  protected $request;

  /**
   * @var DrupalKernelInterface
   */
  protected $kernel;

  /**
   * @param Request $request
   * @param DrupalKernelInterface $kernel
   */
  function __construct(Request $request, DrupalKernelInterface $kernel) {
    $this->request = $request;
    $this->kernel = $kernel;
  }

  /**
   * Handles the request and exits.
   */
  function handleRequestAndExit() {

    $request = $this->request;
    $kernel = $this->kernel;

    // Check if this page is cached.
    // This will exit the request, if a result is found in the cache.
    $kernel->handlePageCache($request);

    // Try a regular, non-cached response.
    $response = $kernel->handle($request);
    $response->prepare($request);
    $response->send();

    // Terminate.
    if ($kernel instanceof TerminableInterface) {
      $kernel->terminate($request, $response);
    }

    // @todo Don't actively exit.
    // The exit only happens to be consistent with the behavior of
    // $kernel->handlePageCache(). Once that is cleaned up, this active exit
    // can be removed.
    exit;
  }
} 
