<?php


namespace Drupal\Core\CoreContainer;

use Drupal\Component\LightContainer\AbstractLightPhaseContainer;
use Drupal\Core\Site\Settings;


/**
 * Each of these magic properties will, if called **for the first time**,
 * trigger the method with 'init' . $name.
 *
 * This may
 * - trigger initialization of dependencies, which can be services, global
 *   state changes, or other things.
 * - trigger the a global state change.
 *
 * @property true SiteNotInstalled
 *   Causes the site directory to be determined.
 *   Throws an exception if site is already installed.
 * @property true SiteSettingsInitialized
 *   Causes the site directory to be determined, site settings to be read and
 *   registered in Settings::* static variable. Throws an exception if site not
 *   installed yet.
 * @property true BootstrapComplete
 *   Causes DrupalKernel::boot(), which also initializes the container.
 * @property true LegacyRequestPrepared
 *   Calls DrupalKernel::prepareLegacyRequest() and all dependencies.
 */
class BootState extends AbstractLightPhaseContainer {

  /**
   * @var CoreServices
   */
  protected $coreServices;

  /**
   * @param CoreServices $core_services
   */
  function __construct(CoreServices $core_services) {
    $this->coreServices = $core_services;
  }

  /**
   * Initializes Settings::$instance
   *
   * @see BootState::SiteSettingsInitialized
   */
  protected function initSiteSettingsInitialized() {
    $site_path = $this->coreServices->SiteDirectory->getSitePath();
    Settings::initialize($site_path);
  }

  /**
   * Makes sure that DrupalKernel::boot() is called.
   *
   * @see BootState::BootstrapComplete
   */
  protected function initBootstrapComplete() {
    $this->coreServices->BootstrappedDrupalKernel;
  }

  /**
   * Makes sure that DrupalKernel::prepareLegacyRequest() was called.
   *
   * This has side effects both on the kernel and on the container, which is
   * accessible via global state.
   *
   * @see BootState::LegacyRequestPrepared
   * @see \Drupal\Core\DrupalKernel::prepareLegacyRequest()
   */
  protected function initLegacyRequestPrepared() {
    $this->coreServices->LegacyPreparedDrupalKernel;
  }

}
