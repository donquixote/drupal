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
 * @property true InstallerProceduralDependenciesIncluded
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

  /**
   * Includes the procedural files needed for the installer.
   *
   * @see StaticContext::InstallerProceduralDependenciesIncluded
   */
  protected function init_InstallerProceduralDependenciesIncluded() {
    $path_to_core = dirname(dirname(dirname(dirname(__DIR__))));
    $path_to_includes = $path_to_core . '/includes';

    require_once $path_to_core . '/modules/system/system.install';
    require_once $path_to_includes . '/common.inc';
    require_once $path_to_includes . '/file.inc';
    require_once $path_to_includes . '/install.inc';
    require_once $path_to_includes . '/schema.inc';
    require_once $path_to_includes . '/path.inc';
    require_once $path_to_includes . '/database.inc';
    require_once $path_to_includes . '/form.inc';
    require_once $path_to_includes . '/batch.inc';

    // Load module basics (needed for hook invokes).
    include_once $path_to_includes . '/module.inc';
    require_once $path_to_includes . '/entity.inc';
  }

}
