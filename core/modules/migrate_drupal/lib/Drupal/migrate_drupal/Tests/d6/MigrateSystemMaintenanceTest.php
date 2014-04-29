<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateSystemMaintenanceTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6SystemMaintenance;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

class MigrateSystemMaintenanceTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate maintenance variables to system.*.yml',
      'description'  => 'Upgrade maintenance variables to system.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_system_maintenance');
    $this->loadDrupal6Dump(new Drupal6SystemMaintenance());
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * Tests migration of system (maintenance) variables to system.maintenance.yml.
   */
  public function testSystemMaintenance() {
    $config = \Drupal::config('system.maintenance');
    $this->assertIdentical($config->get('enable'), 0);
    $this->assertIdentical($config->get('message'), 'Drupal is currently under maintenance. We should be back shortly. Thank you for your patience.');
  }

}
