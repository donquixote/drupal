<?php


namespace Drupal\Core\CoreContainer;

use Drupal\Component\LightContainer\AbstractLightPhaseContainer;
use Drupal\Core\DrupalKernel;


/**
 * All the global state..
 *
 * @property true PhpEnvironmentReady
 * @property true BootstrapIncIncluded
 */
class StaticContext extends AbstractLightPhaseContainer {

  /**
   * Includes bootstrap.inc.
   *
   * @see StaticContext::PhpEnvironmentReady
   */
  function initBootstrapIncIncluded() {
    require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/bootstrap.inc';
  }

  /**
   * Sets up a consistent PHP environment.
   *
   * @see StaticContext::BootstrapIncIncluded
   */
  function initPhpEnvironmentReady() {
    $this->BootstrapIncIncluded;
    // @todo Move this method elsewhere.
    DrupalKernel::bootEnvironment();
  }

}
