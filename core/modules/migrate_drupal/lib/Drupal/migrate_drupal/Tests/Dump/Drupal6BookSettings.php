<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6BookSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing book.settings.yml migration.
 */
class Drupal6BookSettings implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->variableSetMultiple(array(
      'book_allowed_types' => array('book'),
      'book_block_mode' => 'all pages',
      'book_child_type' => 'book',
    ));
  }
}
