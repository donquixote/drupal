<?php

/**
 * @file
 * Definition of Drupal\Core\Database\ExtendedStatementInterface
 */

namespace Drupal\Core\Database;

/**
 * Represents a prepared statement with additional methods.
 */
interface ExtendedStatementInterface extends \Traversable, StatementInterface {

  /**
   * Sets the default fetch mode for this statement.
   *
   * See http://php.net/manual/pdo.constants.php for the definition of the
   * constants used.
   *
   * @param $mode
   *   One of the PDO::FETCH_* constants.
   * @param $a1
   *   An option depending of the fetch mode specified by $mode:
   *   - for PDO::FETCH_COLUMN, the index of the column to fetch
   *   - for PDO::FETCH_CLASS, the name of the class to create
   *   - for PDO::FETCH_INTO, the object to add the data to
   * @param $a2
   *   If $mode is PDO::FETCH_CLASS, the optional arguments to pass to the
   *   constructor.
   */
  public function setFetchMode($mode, $a1 = NULL, $a2 = array());

  /**
   * Fetches the next row from a result set.
   *
   * See http://php.net/manual/pdo.constants.php for the definition of the
   * constants used.
   *
   * @param int|NULL $mode
   *   One of the PDO::FETCH_* constants.
   *   Default to what was specified by setFetchMode().
   * @param $cursor_orientation
   *   Not implemented in all database drivers, don't use.
   * @param $cursor_offset
   *   Not implemented in all database drivers, don't use.
   *
   * @return mixed
   *   A result, formatted according to $mode.
   */
  public function fetch($mode = NULL, $cursor_orientation = NULL, $cursor_offset = NULL);

  /**
   * Fetches the next row and returns it as an object.
   *
   * The object will be of the class specified by StatementInterface::setFetchMode()
   * or stdClass if not specified.
   *
   * @return object|\stdClass|false
   */
  public function fetchObject();

  /**
   * Returns an array containing all of the result set rows.
   *
   * @param int|NULL $mode
   *   One of the PDO::FETCH_* constants.
   * @param int|NULL $column_index
   *   If $mode is PDO::FETCH_COLUMN, the index of the column to fetch.
   * @param array|NULL $constructor_arguments
   *   If $mode is PDO::FETCH_CLASS, the arguments to pass to the constructor.
   *
   * @return array
   *   An array of results.
   */
  function fetchAll($mode = NULL, $column_index = NULL, array $constructor_arguments = NULL);
}
