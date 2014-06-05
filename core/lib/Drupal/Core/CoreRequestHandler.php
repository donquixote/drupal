<?php


namespace Drupal\Core;


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

    $response = $kernel->handle($request);
    $response->prepare($request);
    $response->send();

    // Terminate.
    if ($kernel instanceof TerminableInterface) {
      $kernel->terminate($request, $response);
    }
  }
} 
