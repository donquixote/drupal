<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6ActionSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing action.settings.yml migration.
 */
class Drupal6ActionSettings implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->variableSet('actions_max_stack', 35);
  }

}
