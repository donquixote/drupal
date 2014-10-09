<?php


namespace Drupal\Core\CoreContainer;

use Drupal\Component\MiniContainer\PhaseContainerBase;
use Drupal\Component\MiniContainer\MiniContainerBase;
use Drupal\Component\MiniContainer\MiniContainerException;
use Drupal\Core\DrupalKernel;


/**
 * All the global state..
 *
 * @property true PhpEnvironmentReady
 * @property true BootstrapIncIncluded
 */
class StaticContext extends PhaseContainerBase {

  /**
   * Includes bootstrap.inc.
   *
   * @see StaticContext::PhpEnvironmentReady
   */
  protected function init_BootstrapIncIncluded() {
    require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/bootstrap.inc';
  }

  /**
   * Sets up a consistent PHP environment.
   *
   * @see StaticContext::BootstrapIncIncluded
   */
  protected function init_PhpEnvironmentReady() {
    $this->BootstrapIncIncluded;
    // @todo Move this method elsewhere.
    DrupalKernel::bootEnvironment();
  }

}
