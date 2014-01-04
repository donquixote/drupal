<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateTextConfigsTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Tests migration of variables from the Text module.
 */
class MigrateTextConfigsTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to text.settings.yml',
      'description'  => 'Upgrade variables to text.settings.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Tests migration of text variables to text.settings.yml.
   */
  public function testTextSettings() {
    $migration = entity_load('migration', 'd6_text_settings');
    $dumps = array(
      dirname(__DIR__) . '/Dump/Drupal6TextSettings.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
    $config = \Drupal::config('text.settings');
    $this->assertIdentical($config->get('default_summary_length'), 600);
  }

}
