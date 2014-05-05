<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6ForumSettings.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing forum.site.yml migration.
 */
class Drupal6ForumSettings implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->variableSetMultiple(array(
      'forum_hot_topic' => '15',
      'forum_per_page' => '25',
      'forum_order' => '1',
      'forum_nav_vocabulary' => '1',
      'forum_block_num_0' => '5',
      'forum_block_num_1' => '5',
    ));
  }
}
