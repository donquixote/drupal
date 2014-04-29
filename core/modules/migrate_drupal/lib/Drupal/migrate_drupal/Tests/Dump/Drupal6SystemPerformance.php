<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SystemPerformance.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing system.performance.yml migration.
 */
class Drupal6SystemPerformance implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->ensureTable('variable');
    $dbWrapper->getConnection()->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'preprocess_css',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'preprocess_js',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'cache_lifetime',
      'value' => 'i:0;',
    ))
    ->execute();
  }

}
