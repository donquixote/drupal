<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6FileSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing file.settings.yml migration.
 */
class Drupal6FileSettings implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->variableSetMultiple(array(
      'file_description_type' => 'textfield',
      'file_description_length' => 128,
      'file_icon_directory' => 'sites/default/files/icons',
    ));
  }
}
