<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6NodeSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing node.settings.yml migration.
 */
class Drupal6NodeSettings implements DumpInterface {

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
      'name' => 'node_admin_theme',
      'value' => 'i:0;',
    ))
    ->execute();
  }
}
