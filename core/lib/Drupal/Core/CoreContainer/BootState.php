<?php


namespace Drupal\Core\CoreContainer;

use Drupal\Component\LightContainer\AbstractLightPhaseContainer;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;


/**
 * All the global state..
 *
 * @property true SiteSettingsInitialized
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
   */
  protected function initSiteSettingsInitialized() {
    $site_path = $this->coreServices->SiteDirectory->getSitePath();
    Settings::initialize($site_path);
  }

}
