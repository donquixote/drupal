<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateSystemPerformanceTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6SystemPerformance;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

class MigrateSystemPerformanceTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate performance variables to system.*.yml',
      'description'  => 'Upgrade performance variables to system.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_system_performance');
    $this->loadDrupal6Dump(new Drupal6SystemPerformance());
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * Tests migration of system (Performance) variables to system.performance.yml.
   */
  public function testSystemPerformance() {
    $config = \Drupal::config('system.performance');
    $this->assertIdentical($config->get('css.preprocess'), FALSE);
    $this->assertIdentical($config->get('js.preprocess'), FALSE);
    $this->assertIdentical($config->get('cache.page.max_age'), 0);
  }

}
