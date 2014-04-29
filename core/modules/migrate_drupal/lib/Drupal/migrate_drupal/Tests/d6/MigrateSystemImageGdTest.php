<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateSystemImageGdTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6SystemImageGd;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

class MigrateSystemImageGdTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate image gd variables to system.*.yml',
      'description'  => 'Upgrade image gd variables to system.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_system_image_gd');
    $this->loadDrupal6Dump(new Drupal6SystemImageGd());
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * Tests migration of system (image GD) variables to system.image.gd.yml.
   */
  public function testSystemImageGd() {
    $config = \Drupal::config('system.image.gd');
    $this->assertIdentical($config->get('jpeg_quality'), 75);
  }

}
