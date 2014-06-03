<?php


namespace Drupal\Core\CoreContainer;

use Drupal\Component\LightContainer\AbstractLightContainer;
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Container for low-level services, that do not depend on the dynamically
 * generated container.
 *
 * @property \Composer\Autoload\ClassLoader ClassLoader
 * @property \Symfony\Component\HttpFoundation\Request Request
 * @property \Drupal\Core\DrupalKernel DrupalKernel
 */
class CoreServices extends AbstractLightContainer {

  /**
   * @var bool
   *   FALSE to stop the container from being written to or read from disk.
   */
  protected $allowContainerDumping = TRUE;

  /**
   * @var CoreServiceParameters
   */
  protected $parameters;

  /**
   * Constructs a SiteContainer object.
   */
  public function __construct() {
    $this->parameters = new CoreServiceParameters();
  }

  /**
   * Disables dumping of the container.
   *
   * @return $this
   */
  public function disableContainerDumping() {
    $this->parameters->AllowContainerDumping = FALSE;
    return $this;
  }

  /**
   * @param string $environment
   *
   * @return $this
   */
  public function setEnvironment($environment) {
    $this->parameters->Environment = $environment;
    return $this;
  }

  /**
   * @return Request
   */
  protected function getRequest() {
    return Request::createFromGlobals();
  }

  /**
   * @return \Composer\Autoload\ClassLoader
   */
  protected function getClassLoader() {
    return require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
  }

  /**
   * @return \Drupal\Core\DrupalKernel
   */
  protected function getDrupalKernel() {
    return DrupalKernel::createFromRequest(
      $this->Request,
      $this->ClassLoader,
      $this->parameters->Environment,
      $this->parameters->AllowContainerDumping);
  }

} 
