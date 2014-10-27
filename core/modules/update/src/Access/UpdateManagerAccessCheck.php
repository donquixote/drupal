<?php

/**
 * @file
 * Contains Drupal\update\Access\UpdateManagerAccessCheck.
 */

namespace Drupal\update\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Site\Settings\SettingsInterface;

/**
 * Determines whether allow authorized operations is set.
 */
class UpdateManagerAccessCheck implements AccessInterface {

  /**
   * Settings Service.
   *
   * @var \Drupal\Core\Site\Settings\SettingsInterface
   */
  protected $settings;

  /**
   * Constructs a UpdateManagerAccessCheck object.
   *
   * @param \Drupal\Core\Site\Settings\SettingsInterface $settings
   *   The read-only settings container.
   */
  public function __construct(SettingsInterface $settings) {
    $this->settings = $settings;
  }

  /**
   * Checks access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access() {
    // Uncacheable because the access result depends on a Settings key-value
    // pair, and can therefore change at any time.
    return AccessResult::allowedIf($this->settings->get('allow_authorize_operations', TRUE))->setCacheable(FALSE);
  }

}
