<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateSystemSiteTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateNodeConfigsTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to node.settings.yml',
      'description'  => 'Upgrade variables to node.settings.yml',
      'group' => 'Migrate Drupal',
    );
  }

  function testNodeSettings() {
    $migration = entity_load('migration', 'd6_node_settings');
    $dumps = array(
      dirname(__DIR__) . '/Dump/Drupal6NodeSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage);
    $executable->import();
    $config = \Drupal::config('node.settings');
    $this->assertIdentical($config->get('use_admin_theme'), false);
  }
}
