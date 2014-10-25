<?php


namespace Drupal\Core\CoreContainer;

use Drupal\Component\MiniContainer\PhaseContainerBase;
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
 * @property true SiteSettingsInitialized
 */
class BootState extends PhaseContainerBase {

  /**
   * @var CoreServices
   */
  protected $coreServices;

  /**
   * @param \Drupal\Core\CoreContainer\CoreServices $core_services
   */
  function __construct(CoreServices $core_services) {
    $this->coreServices = $core_services;
  }

  /**
   * Initializes Settings::$instance
   *
   * @see BootState::SiteSettingsInitialized
   */
  protected function init_SiteSettingsInitialized() {
    $site_path = $this->coreServices->SiteDirectory->getSitePath();
    Settings::initialize($site_path, $this->coreServices->ClassLoader);
  }

}
