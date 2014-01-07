<?php

/**
 * @file
 * Contains Drupal\Core\Database\Driver\fake\FakeStatement.
 */

namespace Drupal\Core\Database\Driver\fake;

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
    $return = FALSE;
    if ($this->valid()) {
      $return = $this->current();
      $this->next();
    }
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
  public function fetchObject() {
    $return = $this->fetchAssoc();
    return $return === FALSE ? FALSE : (object) $return;
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
   * {@inheritdoc}
   */
  public function setFetchMode($mode, $a1 = NULL, array $a2 = NULL) {
    // @todo Implement setFetchMode() method.
  }

  /**
   * {@inheritdoc}
   */
  public function fetch($mode = NULL, $cursor_orientation = NULL, $cursor_offset = NULL) {
    // @todo Implement fetch() method.
  }

  /**
   * {@inheritdoc}
   */
  public function fetchObject() {
    // @todo Implement fetchObject() method.
  }

  /**
   * {@inheritdoc}
   */
  function fetchAll($mode = NULL, $column_index = NULL, $constructor_arguments = NULL) {
    // @todo Implement fetchAll() method.
  }
}
