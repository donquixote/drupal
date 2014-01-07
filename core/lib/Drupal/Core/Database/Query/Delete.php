<?php

/**
 * @file
 * Definition of Drupal\Core\Database\Query\Delete
 */

namespace Drupal\Core\Database\Query;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;

/**
 * General class for an abstracted DELETE operation.
 */
class Delete extends Query implements ConditionInterface {

  /**
   * The table from which to delete.
   *
   * @var string
   */
  protected $table;

  /**
   * The condition object for this query.
   *
   * Condition handling is handled via composition.
   *
   * @var Condition
   */
  protected $condition;

  /**
   * Constructs a Delete object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A Connection object.
   * @param string $table
   *   Name of the table to associate with this query.
   * @param array $options
   *   Array of database options.
   */
  public function __construct(Connection $connection, $table, array $options = array()) {
    $options['return'] = Database::RETURN_AFFECTED;
    parent::__construct($connection, $options);
    $this->table = $table;

    $this->condition = new Condition('AND');
  }

  /**
   * {@inheritdoc}
   */
  public function condition($field, $value = NULL, $operator = NULL) {
    $this->condition->condition($field, $value, $operator);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isNull($field) {
    $this->condition->isNull($field);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isNotNull($field) {
    $this->condition->isNotNull($field);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function exists(SelectInterface $select) {
    $this->condition->exists($select);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function notExists(SelectInterface $select) {
    $this->condition->notExists($select);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function &conditions() {
    return $this->condition->conditions();
  }

  /**
   * {@inheritdoc}
   */
  public function arguments() {
    return $this->condition->arguments();
  }

  /**
   * {@inheritdoc}
   */
  public function where($snippet, $args = array()) {
    $this->condition->where($snippet, $args);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function compile(Connection $connection, PlaceholderInterface $queryPlaceholder) {
    $this->condition->compile($connection, $queryPlaceholder);
  }

  /**
   * {@inheritdoc}
   */
  public function compiled() {
    return $this->condition->compiled();
  }

  /**
   * Executes the DELETE query.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   The return value is dependent on the database connection.
   */
  public function execute() {
    $values = array();
    if (count($this->condition)) {
      $this->condition->compile($this->connection, $this);
      $values = $this->condition->arguments();
    }

    return $this->connection->query((string) $this, $values, $this->queryOptions);
  }

  /**
   * Implements PHP magic __toString method to convert the query to a string.
   *
   * @return string
   *   The prepared statement.
   */
  public function __toString() {
    // Create a sanitized comment string to prepend to the query.
    $comments = $this->connection->makeComment($this->comments);

    $query = $comments . 'DELETE FROM {' . $this->connection->escapeTable($this->table) . '} ';

    if (count($this->condition)) {

      $this->condition->compile($this->connection, $this);
      $query .= "\nWHERE " . $this->condition;
    }

    return $query;
  }
}
