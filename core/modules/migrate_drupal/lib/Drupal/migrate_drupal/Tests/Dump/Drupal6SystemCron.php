<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SystemCron.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing system.cron.yml migration.
 */
class Drupal6SystemCron implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->createTable('variable');
    $dbWrapper->getDbConnection()->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'cron_threshold_warning',
      'value' => 'i:172800;',
    ))
    ->values(array(
      'name' => 'cron_threshold_error',
      'value' => 'i:1209600;',
    ))
    ->execute();
  }
}
