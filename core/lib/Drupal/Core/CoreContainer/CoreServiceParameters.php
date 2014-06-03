<?php


namespace Drupal\Core\CoreContainer;

use Drupal\Component\MiniContainer\MiniContainerBase;
use Drupal\Component\MiniContainer\MiniContainerException;

/**
 * Parameters for CoreServices.
 *
 * @property string Environment
 *   Either 'prod' or 'install'.
 * @property bool AllowContainerDumping
 */
class CoreServiceParameters extends MiniContainerBase {

  /**
   * @return string
   *   Either 'prod' or 'install'.
   *
   * @see CoreServiceParameters::Environment
   */
  protected function get_Environment() {
    // Return the default environment.
    return 'prod';
  }

  /**
   * @param mixed $value
   *   Either 'prod' or 'install'.
   *
   * @throws MiniContainerException
   *
   * @see CoreServiceParameters::Environment
   */
  protected function validate_Environment($value) {
    switch ($value) {
      case 'prod':
      case 'install':
        break;
      default:
        throw new MiniContainerException('Environment must be one of "prod" and "install".');
    }
  }

  /**
   * @return bool
   *
   * @see CoreServiceParameters::AllowContainerDumping
   */
  protected function get_AllowContainerDumping() {
    return TRUE;
  }

  /**
   * @param mixed $value
   *
   * @throws MiniContainerException
   *
   * @see CoreServiceParameters::AllowContainerDumping
   */
  protected function validate_AllowContainerDumping($value) {
    if (TRUE !== $value && FALSE !== $value) {
      throw new MiniContainerException('AllowContainerDumping must be either TRUE or FALSE.');
    }
  }
}
