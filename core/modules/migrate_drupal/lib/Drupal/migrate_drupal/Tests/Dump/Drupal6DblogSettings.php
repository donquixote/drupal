<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6DblogSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing dblog.settings.yml migration.
 */
class Drupal6DblogSettings implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->variableSet('dblog_row_limit', 1000);
  }
}
