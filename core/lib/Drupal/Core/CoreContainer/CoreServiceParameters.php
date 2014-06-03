<?php


namespace Drupal\Core\CoreContainer;


use Drupal\Component\LightContainer\AbstractLightContainerParameters;

/**
 * Parameters for CoreServices.
 *
 * @property string Environment
 * @property bool AllowContainerDumping
 */
class CoreServiceParameters extends AbstractLightContainerParameters {

  /**
   * @return string
   */
  protected function getEnvironment() {
    // Return the default environment.
    return 'prod';
  }

  /**
   * @return bool
   */
  protected function getAllowContainerDumping() {
    return TRUE;
  }
} 
