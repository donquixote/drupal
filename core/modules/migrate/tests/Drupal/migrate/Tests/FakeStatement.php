<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeStatement.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\RowCountException;
use Drupal\Core\Database\StatementInterface;

/**
 * Represents a fake prepared statement.
 */
class FakeStatement extends \ArrayIterator implements StatementInterface {

  /**
   * {@inheritdoc}
   */
  public function execute($args = array(), $options = array()) {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryString() {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@inheritdoc}
   */
  public function rowCount() {
    throw new RowCountException();
  }

  /**
   * {@inheritdoc}
   */
  public function fetchField($index = 0) {
    $row = array_values($this->current());
    $return = $row[$index];
    $this->next();
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAssoc() {
    $return = $this->current();
    $this->next();
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchCol($index = 0) {
    $return = array();
    foreach ($this as $row) {
      $row = array_values($row);
      $return[] = $row[$index];
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAllKeyed($key_index = 0, $value_index = 1) {
    $return = array();
    foreach ($this as $row) {
      $row = array_values($row);
      $return[$row[$key_index]] = $row[$value_index];
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAllAssoc($key, $fetch = NULL) {
    $return = array();
    foreach ($this as $row) {
      $return[$row[$key]] = $row;
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Fake statement test',
      'description' => 'Tests for fake statement plugin.',
      'group' => 'Migrate',
    );
  }

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
  public function setFetchMode($mode, $a1 = NULL, $a2 = array()) {
    // TODO: Implement setFetchMode() method.
  }

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
  public function fetch(
    $mode = NULL,
    $cursor_orientation = NULL,
    $cursor_offset = NULL
  ) {
    // TODO: Implement fetch() method.
  }

  /**
   * Fetches the next row and returns it as an object.
   *
   * The object will be of the class specified by StatementInterface::setFetchMode()
   * or stdClass if not specified.
   *
   * @return object|\stdClass|false
   */
  public function fetchObject() {
    // TODO: Implement fetchObject() method.
  }

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
  function fetchAll(
    $mode = NULL,
    $column_index = NULL,
    array $constructor_arguments = NULL
  ) {
    // TODO: Implement fetchAll() method.
  }
}
