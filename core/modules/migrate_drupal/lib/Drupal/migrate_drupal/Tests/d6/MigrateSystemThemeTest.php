<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateSystemThemeTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6SystemTheme;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

class MigrateSystemThemeTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate theme variables to system.*.yml',
      'description'  => 'Upgrade theme variables to system.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_system_theme');
    $this->loadDrupal6Dump(new Drupal6SystemTheme());
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * Tests migration of system (theme) variables to system.theme.yml.
   */
  public function testSystemTheme() {
    $config = \Drupal::config('system.theme');
    $this->assertIdentical($config->get('admin'), '0');
    $this->assertIdentical($config->get('default'), 'garland');
  }

}
