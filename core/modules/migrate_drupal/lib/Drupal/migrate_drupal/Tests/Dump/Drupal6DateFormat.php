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
    $dbWrapper->variableSetMultiple(array(
      'date_format_long' => '\\L\\O\\N\\G l, F j, Y - H:i',
      'date_format_medium' => '\\M\\E\\D\\I\\U\\M D, m/d/Y - H:i',
      'date_format_short' => '\\S\\H\\O\\R\\T m/d/Y - H:i',
    ));
  }

}
