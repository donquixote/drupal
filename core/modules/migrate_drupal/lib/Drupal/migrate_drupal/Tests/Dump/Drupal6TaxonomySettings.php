<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6TaxonomySettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing taxonomy.settings.yml migration.
 */
class Drupal6TaxonomySettings implements DumpInterface {

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
      'name' => 'taxonomy_override_selector',
      'value' => 'b:0;',
    ))
    ->values(array(
      'name' => 'taxonomy_terms_per_page_admin',
      'value' => 'i:100;',
    ))
    ->execute();
  }
}
