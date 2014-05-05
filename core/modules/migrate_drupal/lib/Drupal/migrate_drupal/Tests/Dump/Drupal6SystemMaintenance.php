<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SystemMaintenance.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing system.maintenance.yml migration.
 */
class Drupal6SystemMaintenance implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->createTable('variable');
    $dbWrapper->getConnection()->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'site_offline',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'site_offline_message',
      'value' => 's:94:"Drupal is currently under maintenance. We should be back shortly. Thank you for your patience.";',
    ))
    ->execute();
  }
}
