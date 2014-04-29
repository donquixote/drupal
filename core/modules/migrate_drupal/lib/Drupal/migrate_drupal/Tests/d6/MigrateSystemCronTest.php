<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateSystemCronTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6SystemCron;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests the Drupal 6 cron variables to Drupal 8 system.cron config migration.
 */
class MigrateSystemCronTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate cron variable to system.*.yml',
      'description'  => 'Upgrade cron variable to system.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_system_cron');
    $this->loadDrupal6Dump(new Drupal6SystemCron());
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * Tests migration of system (cron) variables to system.cron.yml.
   */
  public function testSystemCron() {
    $config = \Drupal::config('system.cron');
    $this->assertIdentical($config->get('threshold.warning'), 172800);
    $this->assertIdentical($config->get('threshold.error'), 1209600);
  }

}
