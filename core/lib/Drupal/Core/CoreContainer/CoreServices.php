<?php


namespace Drupal\Core\CoreContainer;

use Drupal\Component\MiniContainer\MiniContainerBase;
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Container for low-level services, that do not depend on the dynamically
 * generated container.
 *
 * @property \Drupal\Core\CoreContainer\CoreServiceParameters Parameters
 * @property \Composer\Autoload\ClassLoader ClassLoader
 * @property \Symfony\Component\HttpFoundation\Request Request
 * @property \Drupal\Core\DrupalKernel DrupalKernel
 */
class CoreServices extends MiniContainerBase {

  /**
   * @return self
   */
  public static function create() {
    return new self();
  }

  /**
   * Disables dumping of the container.
   *
   * @return $this
   */
  public function disableContainerDumping() {
    $this->Parameters->AllowContainerDumping = FALSE;
    return $this;
  }

  /**
   * @param string $environment
   *   E.g. 'prod' or 'testing'.
   *
   * @return $this
   */
  public function setEnvironment($environment) {
    $this->Parameters->Environment = $environment;
    return $this;
  }

  /**
   * @return \Drupal\Core\CoreContainer\CoreServiceParameters
   *
   * @see CoreServices::Parameters
   */
  protected function get_Parameters() {
    return new CoreServiceParameters();
  }

  /**
   * @return Request
   *
   * @see CoreServices::Request
   */
  protected function get_Request() {
    return Request::createFromGlobals();
  }

  /**
   * @return \Composer\Autoload\ClassLoader
   *
   * @see CoreServices::ClassLoader
   */
  protected function get_ClassLoader() {
    return require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
  }

  /**
   * @return \Drupal\Core\DrupalKernel
   *
   * @see CoreServices::DrupalKernel
   */
  protected function get_DrupalKernel() {
    return DrupalKernel::createFromRequest(
      $this->Request,
      $this->ClassLoader,
      $this->Parameters->Environment,
      $this->Parameters->AllowContainerDumping);
  }

}
