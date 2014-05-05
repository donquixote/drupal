<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SearchSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing forum.site.yml migration.
 */
class Drupal6SearchSettings implements DumpInterface {

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
      'name' => 'minimum_word_size',
      'value' => 's:1:"3";',
    ))
    ->values(array(
      'name' => 'overlap_cjk',
      'value' => 'i:1;',
    ))
    ->values(array(
      'name' => 'search_cron_limit',
      'value' => 's:3:"100";',
    ))
    ->execute();
  }
}
