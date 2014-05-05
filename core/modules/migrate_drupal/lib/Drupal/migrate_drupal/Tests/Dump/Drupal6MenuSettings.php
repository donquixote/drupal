<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6MenuSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing menu_ui.settings.yml migration.
 */
class Drupal6MenuSettings implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->createTable('variable');
    $dbWrapper->setModuleVersion('menu', 6000);
    $dbWrapper->getConnection()->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'menu_primary_links_source',
      'value' => 's:13:"primary-links";',
    ))
    ->values(array(
      'name' => 'menu_secondary_links_source',
      'value' => 's:15:"secondary-links";',
    ))
    ->values(array(
      'name' => 'menu_override_parent_selector',
      'value' => 'b:0;',
    ))
    ->execute();
  }
}
