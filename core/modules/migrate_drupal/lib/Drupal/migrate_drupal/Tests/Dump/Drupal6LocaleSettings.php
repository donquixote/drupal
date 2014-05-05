<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6LocaleSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing locale.settings.yml migration.
 */
class Drupal6LocaleSettings implements DumpInterface {

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
      'name' => 'locale_cache_strings',
      'value' => 'i:1;',
    ))
    ->values(array(
      'name' => 'locale_js_directory',
      'value' => 's:9:"languages";',
    ))
    ->execute();
  }

}
