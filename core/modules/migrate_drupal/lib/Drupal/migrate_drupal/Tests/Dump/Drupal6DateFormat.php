<?php

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing date formats migration.
 */
class Drupal6DateFormat implements DumpInterface {

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
        'name' => 'date_format_long',
        'value' => 's:24:"\\L\\O\\N\\G l, F j, Y - H:i";',
      ))
      ->values(array(
        'name' => 'date_format_medium',
        'value' => 's:27:"\\M\\E\\D\\I\\U\\M D, m/d/Y - H:i";',
      ))
      ->values(array(
        'name' => 'date_format_short',
        'value' => 's:22:"\\S\\H\\O\\R\\T m/d/Y - H:i";',
      ))
      ->execute();
  }

}
