<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6TextSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing text.settings.yml migration.
 */
class Drupal6TextSettings implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->ensureTable('variable');
    // This needs to be a merge to avoid conflicts with Drupal6NodeBodyInstance.
    $dbWrapper->getConnection()->merge('variable')
      ->key(array('name' => 'teaser_length'))
      ->fields(array('value' => 'i:456;'))
      ->execute();
  }
}
