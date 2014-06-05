<?php


namespace Drupal\Core\CoreContainer;

use Drupal\Component\LightContainer\AbstractLightContainer;
use Drupal\Core\Database\Database;
use Drupal\Core\DrupalKernel;
use Drupal\Core\DrupalKernel\SiteDrupalKernelInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Site\SiteDirectory;
use Drupal\Core\Site\SitePathFinder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Container for low-level services, that do not depend on the dynamically
 * generated container.
 *
 * @property \Drupal\Core\CoreContainer\StaticContext StaticContext
 * @property \Composer\Autoload\ClassLoader ClassLoader
 * @property \Symfony\Component\HttpFoundation\Request Request
 * @property \Drupal\Core\DrupalKernel\RawDrupalKernelInterface RawDrupalKernel
 * @property \Drupal\Core\DrupalKernel\SiteDrupalKernelInterface SiteDrupalKernel
 * @property \Drupal\Core\DrupalKernel BootstrappedDrupalKernel
 * @property \Drupal\Core\Site\SiteDirectory SiteDirectory
 * @property \Drupal\Core\Site\Settings SiteSettings
 * @property \Drupal\Core\CoreContainer\BootState BootState
 * @property \Drupal\Core\Site\SitePathFinder SitePathFinder
 * @property \Symfony\Component\DependencyInjection\ContainerInterface Container
 * @property \Drupal\Core\CoreRequestHandler CoreRequestHandler
 */
class CoreServices extends AbstractLightContainer {

  /**
   * @var CoreServiceParameters
   */
  protected $parameters;

  /**
   * Constructs a SiteContainer object.
   */
  public function __construct() {
    $this->parameters = new CoreServiceParameters($this);
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
   * Sets a custom site path.
   *
   * This will prevent the site path from being determined dynamically, and will
   * be useful for tests.
   *
   * @param string $site_path
   *
   * @return $this
   */
  public function setCustomSitePath($site_path) {
    $this->parameters->SitePath = $site_path;
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
   * @return \Drupal\Core\CoreContainer\StaticContext
   */
  protected function getStaticContext() {
    return new StaticContext();
  }

  /**
   * @return BootState
   */
  protected function getBootState() {
    return new BootState($this);
  }

  /**
   * @return \Drupal\Core\DrupalKernel\RawDrupalKernelInterface
   */
  protected function getRawDrupalKernel() {

    // Include our bootstrap file.
    $this->StaticContext->BootstrapIncIncluded;

    return new DrupalKernel(
      $this->parameters->Environment,
      $this->ClassLoader,
      $this->parameters->AllowContainerDumping);
  }

  /**
   * @return SitePathFinder
   */
  protected function getSitePathFinder() {
    return new SitePathFinder();
  }

  /**
   * @return \Drupal\Core\Site\SiteDirectory
   */
  protected function getSiteDirectory() {
    return new SiteDirectory($this->parameters->SitePath);
  }

  /**
   * @return \Drupal\Core\Site\Settings
   */
  protected function getSiteSettings() {
    $this->BootState->SiteSettingsInitialized;
    return Settings::getInstance();
  }

  /**
   * @return \Drupal\Core\DrupalKernel\SiteDrupalKernelInterface
   */
  protected function getSiteDrupalKernel() {

    // Ensure sane php environment variables..
    $this->StaticContext->PhpEnvironmentReady;

    // Get our most basic settings setup.
    $this->BootState->SiteSettingsInitialized;

    $kernel = $this->RawDrupalKernel->setSitePath($this->SiteDirectory->getSitePath());

    // @todo This is a weird place to do this.
    // Redirect the user to the installation script if Drupal has not been
    // installed yet (i.e., if no $databases array has been defined in the
    // settings.php file) and we are not already installing.
    if (!Database::getConnectionInfo() && !drupal_installation_attempted() && !drupal_is_cli()) {
      $response = new RedirectResponse($this->Request->getBasePath() . '/core/install.php');
      $response->prepare($this->Request)->send();
    }

    return $kernel;
  }

  /**
   * Returns a bootstrapped Drupal kernel.
   *
   * @return \Drupal\Core\DrupalKernel
   *
   * @throws \Exception
   */
  protected function getBootstrappedDrupalKernel() {
    return $this->SiteDrupalKernel->boot();
  }

  /**
   * @return \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected function getContainer() {
    return $this->BootstrappedDrupalKernel->getContainer();
  }

} 
