<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6StatisticsSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing statistics.settings.yml migration.
 */
class Drupal6StatisticsSettings implements DumpInterface {

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
      'name' => 'statistics_enable_access_log',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'statistics_flush_accesslog_timer',
      'value' => 'i:259200;',
    ))
    ->values(array(
      'name' => 'statistics_count_content_views',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'statistics_block_top_day_num',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'statistics_block_top_all_num',
      'value' => 'i:0;',
    ))
    ->values(array(
      'name' => 'statistics_block_top_last_num',
      'value' => 'i:0;',
    ))
    ->execute();
  }
}
