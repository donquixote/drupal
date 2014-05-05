<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SystemFile.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing system.file.yml migration.
 */
class Drupal6SystemFileStandalone implements DumpInterface {

  /**
   * Dump for the standalone test in MigrateFileTest.
   *
   * @param Drupal6DbWrapper $dbWrapper
   *
   * @throws \Exception
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->createTable('variable');
    $dbWrapper->getConnection()->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'file_directory_path',
      'value' => 's:10:"files/test";',
    ))
    ->execute();
  }

}
