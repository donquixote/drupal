<?php

/**
 * @file
 * Definition of Drupal\Core\Database\IntegrityConstraintViolationException
 */

namespace Drupal\Core\Database;

/**
 * Exception thrown if a query would violate an integrity constraint.
 *
 * This exception is thrown e.g. when trying to insert a row that would violate
 * a unique key constraint.
 */
class IntegrityConstraintViolationException extends \RuntimeException implements DatabaseException {

  /**
   * The query string that caused the exception.
   *
   * @var string
   */
  public $query_string;

  /**
   * The replacement arguments.
   *
   * @var array
   */
  public $args;
}
