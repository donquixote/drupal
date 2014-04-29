<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateFilterFormatTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6FilterFormat;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests the Drupal 6 filter format to Drupal 8 migration.
 */
class MigrateFilterFormatTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  static $modules = array('filter');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate variables to filter.formats.*.yml',
      'description'  => 'Upgrade variables to filter.formats.*.yml',
      'group' => 'Migrate Drupal',
    );
  }


  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_filter_format');
    $this->loadDrupal6Dump(new Drupal6FilterFormat());
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests the Drupal 6 filter format to Drupal 8 migration.
   */
  public function testFilterFormat() {
    $filter_format = entity_load('filter_format', 'filtered_html');

    // Check filter status.
    $filters = $filter_format->get('filters');
    $this->assertTrue($filters['filter_autop']['status']);
    $this->assertTrue($filters['filter_url']['status']);
    $this->assertTrue($filters['filter_htmlcorrector']['status']);
    $this->assertTrue($filters['filter_html']['status']);

    // These should be false by default.
    $this->assertFalse($filters['filter_html_escape']['status']);
    $this->assertFalse($filters['filter_caption']['status']);
    $this->assertFalse($filters['filter_html_image_secure']['status']);

    // Check variables migrated into filter.
    $this->assertIdentical($filters['filter_html']['settings']['allowed_html'], '<a> <em> <strong> <cite> <code> <ul> <ol> <li> <dl> <dt> <dd>');
    $this->assertIdentical($filters['filter_html']['settings']['filter_html_help'], TRUE);
    $this->assertIdentical($filters['filter_html']['settings']['filter_html_nofollow'], FALSE);
    $this->assertIdentical($filters['filter_url']['settings']['filter_url_length'], 72);
  }

}
