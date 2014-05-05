<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SimpletestSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing simpletest.settings.yml migration.
 */
class Drupal6SimpletestSettings implements DumpInterface {

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
      'name' => 'simpletest_clear_results',
      'value' => 'b:1;',
    ))
    ->values(array(
      'name' => 'simpletest_httpauth_method',
      'value' => 'i:1;',
    ))
    ->values(array(
      'name' => 'simpletest_httpauth_password',
      'value' => 'N;',
    ))
    ->values(array(
      'name' => 'simpletest_httpauth_username',
      'value' => 'N;',
    ))
    ->values(array(
      'name' => 'simpletest_verbose',
      'value' => 'b:1;',
    ))
    ->execute();
  }
}
