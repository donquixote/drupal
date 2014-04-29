<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\MigrateDrupalTestBase.
 */

namespace Drupal\migrate_drupal\Tests;

use Drupal\Core\Database\Database;
use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;
use Drupal\migrate_drupal\Tests\Dump\DumpInterface;

/**
 * Base test class for migrations from Drupal 6.
 */
class MigrateDrupal6TestBase extends MigrateDrupalTestBase {

  /**
   * A wrapper for the Drupal 6 database, with additional methods to create
   * tables and enable modules.
   *
   * @var Drupal6DbWrapper
   */
  protected $drupal6DbWrapper;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $drupal6Database = Database::getConnection('default', 'migrate');
    $this->drupal6DbWrapper = new Drupal6DbWrapper($drupal6Database);
  }

  /**
   * Loads a dump into the Drupal 6 database.
   *
   * @param DumpInterface $dump
   */
  protected function loadDrupal6Dump(DumpInterface $dump) {
    $dump->load($this->drupal6DbWrapper);
  }

  /**
   * Loads a number of dumps into the Drupal 6 database.
   *
   * @param DumpInterface[] $dumps
   */
  protected function loadDrupal6Dumps($dumps) {
    foreach ($dumps as $dump) {
      $dump->load($this->drupal6DbWrapper);
    }
  }

}
