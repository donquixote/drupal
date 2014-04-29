<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateSimpletestConfigsTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateMessage;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6SimpletestSettings;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests migration of variables from the Simpletest module.
 */
class MigrateSimpletestConfigsTest extends MigrateDrupal6TestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('simpletest');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to simpletest.settings.yml',
      'description'  => 'Upgrade variables to simpletest.settings.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_simpletest_settings');
    $this->loadDrupal6Dump(new Drupal6SimpletestSettings());
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * Tests migration of simpletest variables to simpletest.settings.yml.
   */
  public function testSimpletestSettings() {
    $config = \Drupal::config('simpletest.settings');
    $this->assertIdentical($config->get('clear_results'), TRUE);
    $this->assertIdentical($config->get('httpauth.method'), CURLAUTH_BASIC);
    // NULL in the dump means defaults which is empty string. Same as omitting
    // them.
    $this->assertIdentical($config->get('httpauth.password'), '');
    $this->assertIdentical($config->get('httpauth.username'), '');
    $this->assertIdentical($config->get('verbose'), TRUE);
  }
}
