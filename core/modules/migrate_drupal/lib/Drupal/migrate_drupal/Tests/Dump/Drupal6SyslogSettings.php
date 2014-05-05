<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SyslogSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing syslog.settings.yml migration.
 */
class Drupal6SyslogSettings implements DumpInterface {

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
      'name' => 'syslog_facility',
      'value' => 'i:128;',
    ))
    ->values(array(
      'name' => 'syslog_identity',
      'value' => 's:6:"drupal";',
    ))
    ->execute();
  }
}
