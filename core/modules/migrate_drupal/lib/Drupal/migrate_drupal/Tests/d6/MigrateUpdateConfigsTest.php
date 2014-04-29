<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUpdateConfigsTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6UpdateSettings;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests migration of variables from the Update module.
 */
class MigrateUpdateConfigsTest extends MigrateDrupal6TestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('update');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to update.settings.yml',
      'description'  => 'Upgrade variables to update.settings.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_update_settings');
    $this->loadDrupal6Dump(new Drupal6UpdateSettings());
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests migration of update variables to update.settings.yml.
   */
  public function testUpdateSettings() {
    $config = \Drupal::config('update.settings');
    $this->assertIdentical($config->get('fetch.max_attempts'), 2);
    $this->assertIdentical($config->get('fetch.url'), 'http://updates.drupal.org/release-history');
    $this->assertIdentical($config->get('notification.threshold'), 'all');
    $this->assertIdentical($config->get('notification.mails'), array());
  }
}
