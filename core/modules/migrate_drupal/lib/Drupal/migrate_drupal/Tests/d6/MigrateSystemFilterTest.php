<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateSystemFilterTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6SystemFilter;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests the Drupal 6 system filter variables to Drupal 8 system.filter config migration.
 */
class MigrateSystemFilterTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate filter variables to system.*.yml',
      'description'  => 'Upgrade filter variables to system.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_system_filter');
    $this->loadDrupal6Dump(new Drupal6SystemFilter());
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * Tests migration of system (filter) variables to system.filter.yml.
   */
  public function testSystemFilter() {
    $config = \Drupal::config('system.filter');
    $this->assertIdentical($config->get('protocols'), array('http', 'https', 'ftp', 'news', 'nntp', 'tel', 'telnet', 'mailto', 'irc', 'ssh', 'sftp', 'webcal', 'rtsp'));
  }

}
