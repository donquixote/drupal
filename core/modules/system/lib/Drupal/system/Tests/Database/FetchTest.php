<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Database\FetchTest.
 */

namespace Drupal\system\Tests\Database;

use Drupal\Core\Database\RowCountException;
use Drupal\Core\Database\StatementInterface;

/**
 * Tests fetch actions.
 *
 * We get timeout errors if we try to run too many tests at once.
 */
class FetchTest extends DatabaseTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Fetch tests',
      'description' => 'Test the Database system\'s various fetch capabilities.',
      'group' => 'Database',
    );
  }

  /**
   * Confirms that we can fetch a record properly in default object mode.
   */
  function testQueryFetchDefault() {
    $records = array();
    $result = db_query('SELECT name FROM {test} WHERE age = :age', array(':age' => 25));
    $this->assertTrue($result instanceof StatementInterface, 'Result set is a Drupal statement object.');
    foreach ($result as $record) {
      $records[] = $record;
      $this->assertTrue(is_object($record), 'Record is an object.');
      $this->assertIdentical($record->name, 'John', '25 year old is John.');
    }

    $this->assertIdentical(count($records), 1, 'There is only one record.');
  }

  /**
   * Confirms that we can fetch a record to an object explicitly.
   */
  function testQueryFetchObject() {
    $records = array();
    $result = db_query('SELECT name FROM {test} WHERE age = :age', array(':age' => 25), array('fetch' => \PDO::FETCH_OBJ));
    foreach ($result as $record) {
      $records[] = $record;
      $this->assertTrue(is_object($record), 'Record is an object.');
      $this->assertIdentical($record->name, 'John', '25 year old is John.');
    }

    $this->assertIdentical(count($records), 1, 'There is only one record.');
  }

  /**
   * Confirms that we can fetch a record to an associative array explicitly.
   */
  function testQueryFetchArray() {
    $records = array();
    $result = db_query('SELECT name FROM {test} WHERE age = :age', array(':age' => 25), array('fetch' => \PDO::FETCH_ASSOC));
    foreach ($result as $record) {
      $records[] = $record;
      if ($this->assertTrue(is_array($record), 'Record is an array.')) {
        $this->assertIdentical($record['name'], 'John', 'Record can be accessed associatively.');
      }
    }

    $this->assertIdentical(count($records), 1, 'There is only one record.');
  }

  /**
   * Confirms that we can fetch a record into a new instance of a custom class.
   *
   * @see \Drupal\system\Tests\Database\FakeRecord
   */
  function testQueryFetchClass() {
    $records = array();
    $result = db_query('SELECT name FROM {test} WHERE age = :age', array(':age' => 25), array('fetch' => 'Drupal\system\Tests\Database\FakeRecord'));
    foreach ($result as $record) {
      $records[] = $record;
      if ($this->assertTrue($record instanceof FakeRecord, 'Record is an object of class FakeRecord.')) {
        $this->assertIdentical($record->name, 'John', '25 year old is John.');
      }
    }

    $this->assertIdentical(count($records), 1, 'There is only one record.');
  }

  /**
   * Confirm that we can fetch a record into a new instance of a custom class.
   * The name of the class is determined from a value of the first column.
   *
   * @see Drupal\system\Tests\Database\FakeRecord
   */
  function testQueryFetchClassType() {
    $records = array();
    $result = db_query('SELECT classname, name, job FROM {test_classtype} WHERE age = :age', array(':age' => 26));
    foreach ($result->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE) as $record) {
      if ($this->assertTrue($record instanceof FakeRecord, 'Record is an object of class FakeRecord.')) {
        $this->assertIdentical($record->name, 'Kay', 'Kay is found.');
        $this->assertIdentical($record->job, 'Web Developer', 'A 26 year old Web Developer.');
      }
      $this->assertTrue(!isset($record->classname), 'Classname field not found, as intended.');
      $records[] = $record;
    }

    $this->assertIdentical(count($records), 1, 'There is exactly one record.');
  }

  /**
   * Confirms that we can fetch a record into an indexed array explicitly.
   */
  function testQueryFetchNum() {
    $records = array();
    $result = db_query('SELECT name FROM {test} WHERE age = :age', array(':age' => 25), array('fetch' => \PDO::FETCH_NUM));
    foreach ($result as $record) {
      $records[] = $record;
      if ($this->assertTrue(is_array($record), 'Record is an array.')) {
        $this->assertIdentical($record[0], 'John', 'Record can be accessed numerically.');
      }
    }

    $this->assertIdentical(count($records), 1, 'There is only one record');
  }

  /**
   * Confirms that we can fetch a record into a doubly-keyed array explicitly.
   */
  function testQueryFetchBoth() {
    $records = array();
    $result = db_query('SELECT name FROM {test} WHERE age = :age', array(':age' => 25), array('fetch' => \PDO::FETCH_BOTH));
    foreach ($result as $record) {
      $records[] = $record;
      if ($this->assertTrue(is_array($record), 'Record is an array.')) {
        $this->assertIdentical($record[0], 'John', 'Record can be accessed numerically.');
        $this->assertIdentical($record['name'], 'John', 'Record can be accessed associatively.');
      }
    }

    $this->assertIdentical(count($records), 1, 'There is only one record.');
  }

  /**
   * Confirms that we can fetch an entire column of a result set at once.
   */
  function testQueryFetchCol() {
    $result = db_query('SELECT name FROM {test} WHERE age > :age', array(':age' => 25));
    $column = $result->fetchCol();
    $this->assertIdentical(count($column), 3, 'fetchCol() returns the right number of records.');

    $result = db_query('SELECT name FROM {test} WHERE age > :age', array(':age' => 25));
    $i = 0;
    foreach ($result as $record) {
      $this->assertIdentical($record->name, $column[$i++], 'Column matches direct accesss.');
    }
  }

  /**
   * Tests that rowCount() throws exception on SELECT query.
   */
  public function testRowCount() {
    $result = db_query('SELECT name FROM {test}');
    try {
      $result->rowCount();
      $exception = FALSE;
    }
    catch (RowCountException $e) {
      $exception = TRUE;
    }
    $this->assertTrue($exception, 'Exception was thrown');
  }

}
