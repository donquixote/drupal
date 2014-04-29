<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateAggregatorConfigsTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6AggregatorSettings;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests migration of variables from the Aggregator module.
 */
class MigrateAggregatorConfigsTest extends MigrateDrupal6TestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('aggregator');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to aggregator.settings.yml',
      'description'  => 'Upgrade variables to aggregator.settings.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_aggregator_settings');
    $this->loadDrupal6Dump(new Drupal6AggregatorSettings());
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests migration of aggregator variables to aggregator.settings.yml.
   */
  public function testAggregatorSettings() {
    $config = \Drupal::config('aggregator.settings');
    $this->assertIdentical($config->get('fetcher'), 'aggregator');
    $this->assertIdentical($config->get('parser'), 'aggregator');
    $this->assertIdentical($config->get('processors'), array('aggregator'));
    $this->assertIdentical($config->get('items.teaser_length'), 600);
    $this->assertIdentical($config->get('items.allowed_html'), '<a> <b> <br /> <dd> <dl> <dt> <em> <i> <li> <ol> <p> <strong> <u> <ul>');
    $this->assertIdentical($config->get('items.expire'), 9676800);
    $this->assertIdentical($config->get('source.list_max'), 3);
    $this->assertIdentical($config->get('source.category_selector'), 'checkboxes');
  }

}
