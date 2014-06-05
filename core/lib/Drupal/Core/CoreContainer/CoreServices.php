<?php


namespace Drupal\Core\CoreContainer;

use Drupal\Component\MiniContainer\MiniContainerBase;
use Drupal\Core\CoreRequestHandler;
use Drupal\Core\Database\Database;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Drupal\Core\Site\SiteDirectory;
use Drupal\Core\Site\SitePathFinder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Container for low-level services, that do not depend on the dynamically
 * generated container.
 *
 * @property \Drupal\Core\CoreContainer\CoreServiceParameters Parameters
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
 * @property \Drupal\Core\DrupalKernel LegacyPreparedDrupalKernel
 *   A DrupalKernel where prepareLegacyRequest() was called.
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
   * Sets a custom site path.
   *
   * This will prevent the site path from being determined dynamically, and will
   * be useful for tests.
   *
   * @param string $site_path
   *   E.g. 'sites/default'.
   *
   * @return $this
   */
  public function setCustomSitePath($site_path) {
    $this->Parameters->SitePath = $site_path;
    return $this;
  }

  /**
   * @return \Drupal\Core\CoreContainer\CoreServiceParameters
   *
   * @see CoreServices::Parameters
   */
  protected function get_Parameters() {
    return new CoreServiceParameters($this);
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
   * @return \Drupal\Core\CoreContainer\StaticContext
   *
   * @see CoreServices::StaticContext
   */
  protected function get_StaticContext() {
    return new StaticContext();
  }

  /**
   * @return BootState
   *
   * @see CoreServices::BootState
   */
  protected function get_BootState() {
    return new BootState($this);
  }

  /**
   * @return \Drupal\Core\DrupalKernel\RawDrupalKernelInterface
   *
   * @see CoreServices::RawDrupalKernel
   */
  protected function get_RawDrupalKernel() {

    // Include our bootstrap file.
    $this->StaticContext->BootstrapIncIncluded;

    // @todo Different DrupalKernel class depending on situation.
    return new DrupalKernel(
      $this->Parameters->Environment,
      $this->ClassLoader,
      $this->Parameters->AllowContainerDumping);
  }

  /**
   * @return SitePathFinder
   *
   * @see CoreServices::SitePathFinder
   */
  protected function get_SitePathFinder() {
    return new SitePathFinder();
  }

  /**
   * Wrapper for the site path.
   *
   * @return \Drupal\Core\Site\SiteDirectory
   *
   * @see CoreServices::SiteDirectory
   */
  protected function get_SiteDirectory() {
    return new SiteDirectory($this->Parameters->SitePath);
  }

  /**
   * Same as SiteDirectory, but with exception if site already installed.
   *
   * @return SiteDirectory
   *
   * @see CoreServices::EmptySiteDirectory
   */
  protected function get_EmptySiteDirectory() {
    $this->BootState->SiteNotInstalled;
  }

  /**
   * Same as SiteDirectory, but with exception if settings.php is missing.
   *
   * @return SiteDirectory
   *
   * @see CoreServices::InstalledSiteDirectory
   */
  protected function get_InstalledSiteDirectory() {
    $this->BootState->SiteSettingsInitialized;
    return $this->SiteDirectory;
  }

  /**
   * @return \Drupal\Core\Site\Settings
   *
   * @see CoreServices::SiteSettings
   */
  protected function get_SiteSettings() {
    $this->BootState->SiteSettingsInitialized;
    return Settings::getInstance();
  }

  /**
   * @return \Drupal\Core\DrupalKernel\SiteDrupalKernelInterface
   *
   * @see CoreServices::SiteDrupalKernel
   */
  protected function get_SiteDrupalKernel() {

    // Ensure sane php environment variables..
    $this->StaticContext->PhpEnvironmentReady;

    // Get our most basic settings setup.
    $this->BootState->SiteSettingsInitialized;

    $kernel = $this->RawDrupalKernel->setSitePath($this->SiteDirectory->getSitePath());

    // @todo This is a weird place to do this.
    // Redirect the user to the installation script if Drupal has not been
    // installed yet (i.e., if no $databases array has been defined in the
    // settings.php file) and we are not already installing.
    if ( !Database::getConnectionInfo()
      && !drupal_installation_attempted()
      && PHP_SAPI !== 'cli'
    ) {
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
   *
   * @see CoreServices::BootstrappedDrupalKernel
   */
  protected function get_BootstrappedDrupalKernel() {
    return $this->SiteDrupalKernel->boot();
  }

  /**
   * Returns a bootstrapped Drupal kernel where prepareLegacyRequest() was
   * called.
   *
   * @return DrupalKernel
   *
   * @see CoreServices::LegacyPreparedDrupalKernel
   */
  protected function get_LegacyPreparedDrupalKernel() {
    $kernel = $this->BootstrappedDrupalKernel;
    $kernel->prepareLegacyRequest($this->Request);
    return $kernel;
  }

  /**
   * @return \Symfony\Component\DependencyInjection\ContainerInterface
   *
   * @see CoreServices::Container
   */
  protected function get_Container() {
    return $this->BootstrappedDrupalKernel->getContainer();
  }

  /**
   * @return \Drupal\Core\CoreRequestHandler
   *
   * @see CoreServices::CoreRequestHandler
   */
  protected function get_CoreRequestHandler() {
    return new CoreRequestHandler($this->Request, $this->BootstrappedDrupalKernel);
  }

}
