<?php

/**
 * @file
 * Definition of Drupal\Core\Database\Database
 */

namespace Drupal\Core\Database;

use Drupal\Core\Database\Manager\DatabaseManager;

/**
 * Primary front-controller for the database system.
 *
 * This class is uninstantiatable and un-extendable. It acts to encapsulate
 * all control and shepherding of database connections into a single location
 * without the use of globals.
 *
 * @see \Drupal\Core\Database\Manager\DatabaseManager
 */
abstract class Database {

  /**
   * Flag to indicate a query call should simply return NULL.
   *
   * This is used for queries that have no reasonable return value anyway, such
   * as INSERT statements to a table without a serial primary key.
   */
  const RETURN_NULL = 0;

  /**
   * Flag to indicate a query call should return the prepared statement.
   */
  const RETURN_STATEMENT = 1;

  /**
   * Flag to indicate a query call should return the number of affected rows.
   */
  const RETURN_AFFECTED = 2;

  /**
   * Flag to indicate a query call should return the "last insert id".
   */
  const RETURN_INSERT_ID = 3;

  /**
   * @var \Drupal\Core\Database\Manager\DatabaseManager
   */
  protected static $databaseManager;

  /**
   * Gets the static instance of self::$databaseManager, and lazy-instantiates
   * it if needed.
   *
   * @return \Drupal\Core\Database\Manager\DatabaseManager
   */
  public static function getDatabaseManager() {
    return isset(self::$databaseManager)
      ? self::$databaseManager
      : self::$databaseManager = new DatabaseManager();
  }

  /**
   * Starts logging a given logging key on the specified connection.
   *
   * @param string $logging_key
   *   The logging key to log.
   * @param string $key
   *   The database connection key for which we want to log.
   *
   * @return \Drupal\Core\Database\Log
   *   The query log object. Note that the log object does support richer
   *   methods than the few exposed through the Database class, so in some
   *   cases it may be desirable to access it directly.
   *
   * @see \Drupal\Core\Database\Log
   */
  final public static function startLog($logging_key, $key = 'default') {
    return self::getDatabaseManager()->startLog($logging_key, $key);
  }

  /**
   * Retrieves the queries logged on for given logging key.
   *
   * This method also ends logging for the specified key. To get the query log
   * to date without ending the logger request the logging object by starting
   * it again (which does nothing to an open log key) and call methods on it as
   * desired.
   *
   * @param string $logging_key
   *   The logging key to log.
   * @param string $key
   *   The database connection key for which we want to log.
   *
   * @return array
   *   The query log for the specified logging key and connection.
   *
   * @see \Drupal\Core\Database\Log
   */
  final public static function getLog($logging_key, $key = 'default') {
    return self::getDatabaseManager()->getLog($logging_key, $key);
  }

  /**
   * Gets the connection object for the specified database key and target.
   *
   * @param string $target
   *   The database target name.
   * @param string $key
   *   The database connection key. Defaults to NULL which means the active key.
   *
   * @return \Drupal\Core\Database\Connection
   *   The corresponding connection object.
   */
  final public static function getConnection($target = 'default', $key = NULL) {
    return self::getDatabaseManager()->getConnection($target, $key);
  }

  /**
   * Determines if there is an active connection.
   *
   * Note that this method will return FALSE if no connection has been
   * established yet, even if one could be.
   *
   * @return bool
   *   TRUE if there is at least one database connection established, FALSE
   *   otherwise.
   */
  final public static function isActiveConnection() {
    return self::getDatabaseManager()->isActiveConnection();
  }

  /**
   * Sets the active connection to the specified key.
   *
   * @param string $key
   *
   * @return null|string
   *   The previous database connection key.
   */
  final public static function setActiveConnection($key = 'default') {
    return self::getDatabaseManager()->setActiveConnection($key);
  }

  /**
   * Process the configuration file for database information.
   *
   * @param array $info
   *   The database connection information, as defined in settings.php. The
   *   structure of this array depends on the database driver it is connecting
   *   to.
   *
   * @return array
   *   The modified $info array with database connection information.
   */
  final public static function parseConnectionInfo(array $info) {
    return self::getDatabaseManager()->parseConnectionInfo($info);
  }

  /**
   * Adds database connection information for a given key/target.
   *
   * This method allows to add new connections at runtime.
   *
   * Under normal circumstances the preferred way to specify database
   * credentials is via settings.php. However, this method allows them to be
   * added at arbitrary times, such as during unit tests, when connecting to
   * admin-defined third party databases, etc.
   *
   * If the given key/target pair already exists, this method will be ignored.
   *
   * @param string $key
   *   The database key.
   * @param string $target
   *   The database target name.
   * @param array $info
   *   The database connection information, as defined in settings.php. The
   *   structure of this array depends on the database driver it is connecting
   *   to.
   */
  final public static function addConnectionInfo($key, $target, array $info) {
    self::getDatabaseManager()->addConnectionInfo($key, $target, $info);
  }

  /**
   * Gets information on the specified database connection.
   *
   * @param string $key
   *   (optional) The connection key for which to return information.
   *
   * @return array[]|null
   */
  final public static function getConnectionInfo($key = 'default') {
    return self::getDatabaseManager()->getConnectionInfo($key);
  }

  /**
   * Gets connection information for all available databases.
   *
   * @return array[][]
   */
  final public static function getAllConnectionInfo() {
    return self::getDatabaseManager()->getAllConnectionInfo();
  }

  /**
   * Sets connection information for multiple databases.
   *
   * @param array[][] $databases
   *   A multi-dimensional array specifying database connection parameters, as
   *   defined in settings.php.
   */
  final public static function setMultipleConnectionInfo(array $databases) {
    self::getDatabaseManager()->setMultipleConnectionInfo($databases);
  }

  /**
   * Rename a connection and its corresponding connection information.
   *
   * @param string $old_key
   *   The old connection key.
   * @param string $new_key
   *   The new connection key.
   *
   * @return bool
   *   TRUE in case of success, FALSE otherwise.
   */
  final public static function renameConnection($old_key, $new_key) {
    return self::getDatabaseManager()->renameConnection($old_key, $new_key);
  }

  /**
   * Remove a connection and its corresponding connection information.
   *
   * @param string $key
   *   The connection key.
   *
   * @return bool
   *   TRUE in case of success, FALSE otherwise.
   */
  final public static function removeConnection($key) {
    return self::getDatabaseManager()->removeConnection($key);
  }

  /**
   * Closes a connection to the server specified by the given key and target.
   *
   * @param string $target
   *   The database target name.  Defaults to NULL meaning that all target
   *   connections will be closed.
   * @param string $key
   *   The database connection key. Defaults to NULL which means the active key.
   */
  public static function closeConnection($target = NULL, $key = NULL) {
    self::getDatabaseManager()->closeConnection($target, $key);
  }

  /**
   * Instructs the system to temporarily ignore a given key/target.
   *
   * At times we need to temporarily disable replica queries. To do so, call this
   * method with the database key and the target to disable. That database key
   * will then always fall back to 'default' for that key, even if it's defined.
   *
   * @param string $key
   *   The database connection key.
   * @param string $target
   *   The target of the specified key to ignore.
   */
  public static function ignoreTarget($key, $target) {
    self::getDatabaseManager()->ignoreTarget($key, $target);
  }
}
