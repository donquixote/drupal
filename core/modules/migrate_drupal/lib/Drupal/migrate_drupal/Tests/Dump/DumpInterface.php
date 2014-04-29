<?php


namespace Drupal\migrate_drupal\Tests\Dump;


use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Holds data to be loaded into a temporary Drupal 6 database.
 */
interface DumpInterface {

  /**
   * Loads the dump data into the wrapped database.
   *
   * @param \Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper $dbWrapper
   */
  public function load(Drupal6DbWrapper $dbWrapper);
} 
