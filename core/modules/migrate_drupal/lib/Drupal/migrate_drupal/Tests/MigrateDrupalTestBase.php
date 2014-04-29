<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\MigrateDrupalTestBase.
 */

namespace Drupal\migrate_drupal\Tests;

use Drupal\migrate\Tests\MigrateTestBase;

/**
 * Base test class for migrations from Drupal 6 or Drupal 7.
 */
class MigrateDrupalTestBase extends MigrateTestBase {

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  public static $modules = array('migrate_drupal');

}
