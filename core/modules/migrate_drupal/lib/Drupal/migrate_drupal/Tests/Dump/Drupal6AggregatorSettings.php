<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6AggregatorSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing aggregator.settings.yml migration.
 */
class Drupal6AggregatorSettings implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->variableSetMultiple(array(
      'aggregator_fetcher' => 'aggregator',
      'aggregator_parser' => 'aggregator',
      'aggregator_processors' => array('aggregator'),
      'aggregator_allowed_html_tags' => '<a> <b> <br /> <dd> <dl> <dt> <em> <i> <li> <ol> <p> <strong> <u> <ul>',
      'aggregator_teaser_length' => '600',
      'aggregator_clear' => '9676800',
      'aggregator_summary_items' => '3',
      'aggregator_category_selector' => 'checkboxes',
    ));
  }
}
