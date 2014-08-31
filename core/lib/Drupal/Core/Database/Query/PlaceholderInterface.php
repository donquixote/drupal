<?php

/**
 * @file
 * Definition of Drupal\Core\Database\Query\PlaceholderInterface
 */

namespace Drupal\Core\Database\Query;

/**
 * Interface for a query that accepts placeholders.
 */
interface PlaceholderInterface {

  /**
   * Returns a unique identifier for this object.
   *
   * @return string
   */
  public function uniqueIdentifier();

  /**
   * Returns the next placeholder ID for the query.
   *
   * @return int
   *   The next available placeholder ID as an integer.
   */
  public function nextPlaceholder();
}
