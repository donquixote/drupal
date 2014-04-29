<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateSystemSiteTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6NodeSettings;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests Drupal 6 node settings to Drupal 8 migration.
 */
class MigrateNodeConfigsTest extends MigrateDrupal6TestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node');

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

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_node_settings');
    $this->loadDrupal6Dump(new Drupal6NodeSettings());
    $executable = new MigrateExecutable($migration, new MigrateMessage);
    $executable->import();
  }

  /**
   * Tests Drupal 6 node settings to Drupal 8 migration.
   */
  public function testNodeSettings() {
    $config = \Drupal::config('node.settings');
    $this->assertIdentical($config->get('use_admin_theme'), false);
  }
}
