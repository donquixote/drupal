<?php


namespace Drupal\Core\CoreContainer;


use Drupal\Component\LightContainer\AbstractLightContainerParameters;

/**
 * Parameters for CoreServices.
 *
 * @property string Environment
 * @property bool AllowContainerDumping
 * @property string SitePath
 */
class CoreServiceParameters extends AbstractLightContainerParameters {

  /**
   * @var CoreServices
   */
  protected $coreServices;

  /**
   * @param CoreServices $core_services
   */
  public function __construct(CoreServices $core_services) {
    $this->coreServices = $core_services;
  }

  /**
   * @return string
   *
   * @see CoreServiceParameters::Environment
   */
  protected function getEnvironment() {
    // Return the default environment.
    return 'prod';
  }

  /**
   * @return bool
   *
   * @see CoreServiceParameters::AllowContainerDumping
   */
  protected function getAllowContainerDumping() {
    return TRUE;
  }

  /**
   * @return string
   *
   * @see CoreServiceParameters::SitePath
   */
  protected function getSitePath() {
    return $this->coreServices->SitePathFinder->findSitePath(
      $this->coreServices->Request);
  }
} 
