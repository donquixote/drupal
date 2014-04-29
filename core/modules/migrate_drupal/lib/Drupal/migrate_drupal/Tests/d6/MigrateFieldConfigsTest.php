<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateFieldConfigsTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;

use Drupal\migrate_drupal\Tests\Dump\Drupal6FieldSettings;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests migration of variables from the Field module.
 */
class MigrateFieldConfigsTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to field.settings.yml',
      'description'  => 'Upgrade variables to field.settings.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_field_settings');
    $this->loadDrupal6Dump(new Drupal6FieldSettings());
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests migration of field variables to field.settings.yml.
   */
  public function testFieldSettings() {
    $config = \Drupal::config('field.settings');
    $this->assertIdentical($config->get('language_fallback'), TRUE);
  }
}
