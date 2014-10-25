<?php


namespace Drupal\Core\Database\Manager;

use Drupal\Core\Database\Log;

/**
 * Object to manage one or more database connections used in a system.
 *
 * @see \Drupal\Core\Database\Database
 */
class DatabaseManager {

  /**
   * An nested array of all active connections. It is keyed by database name
   * and target.
   *
   * @var \Drupal\Core\Database\Connection[][]
   *   Format: $[$key][$target] = $connection
   */
  protected $connections = array();

  /**
   * Processed database connection information from settings.php, wrapped in
   * ConnectionInfo objects.
   *
   * @var ConnectionInfoPool
   */
  protected $connectionInfoPool = array();

  /**
   * A list of key/target credentials to simply ignore.
   *
   * @var bool[][]
   *   Format: $[$key][$target] = TRUE
   */
  protected $ignoreTargets = array();

  /**
   * The key of the currently active database connection.
   *
   * @var string
   */
  protected $activeKey = 'default';

  /**
   * An array of active query log objects.
   *
   * Every connection has one and only one logger object for all targets and
   * logging keys.
   *
   * @var \Drupal\Core\Database\Log[]
   *   Format: $[$db_key] = $log
   */
  protected $logs = array();

  /**
   * Constructs a DatabaseManager object.
   *
   * @param \Drupal\Core\Database\Manager\ConnectionInfoPool $connection_info_pool
   */
  public function __construct(ConnectionInfoPool $connection_info_pool) {
    $this->connectionInfoPool = $connection_info_pool;
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
  public function startLog($logging_key, $key = 'default') {
    if (empty($this->logs[$key])) {
      $this->logs[$key] = new Log($key);

      // Every target already active for this connection key needs to have the
      // logging object associated with it.
      if (!empty($this->connections[$key])) {
        foreach ($this->connections[$key] as $connection) {
          $connection->setLogger($this->logs[$key]);
        }
      }
    }

    $this->logs[$key]->start($logging_key);
    return $this->logs[$key];
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
  public function getLog($logging_key, $key = 'default') {
    if (empty($this->logs[$key])) {
      return NULL;
    }
    $queries = $this->logs[$key]->get($logging_key);
    $this->logs[$key]->end($logging_key);
    return $queries;
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
  public function getConnection($target = 'default', $key = NULL) {

    if (!isset($key)) {
      // By default, we want the active connection, set in setActiveConnection.
      $key = $this->activeKey;
    }

    // If the requested target does not exist, or if it is ignored, we fall back
    // to the default target. The target is typically either "default" or
    // "replica", indicating to use a replica SQL server if one is available. If
    // it's not available, then the default/primary server is the correct server
    // to use.
    if (!empty($this->ignoreTargets[$key][$target]) || !$this->connectionInfoPool->targetExists($key, $target)) {
      $target = 'default';
    }

    return isset($this->connections[$key][$target])
      ? $this->connections[$key][$target]
      : $this->connections[$key][$target] = $this->openConnection($key, $target);
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
  public function isActiveConnection() {
    return !empty($this->activeKey)
      && !empty($this->connections)
      && !empty($this->connections[$this->activeKey]);
  }

  /**
   * Sets the active connection to the specified key.
   *
   * @param string $key
   *   The key identifying the database connection that should be set as active.
   *
   * @return string|null
   *   The previous database connection key.
   */
  public function setActiveConnection($key = 'default') {
    if ($this->connectionInfoPool->keyExists($key)) {
      $old_key = $this->activeKey;
      $this->activeKey = $key;
      return $old_key;
    }

    return NULL;
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
  public function addConnectionInfo($key, $target, array $info) {
    $this->connectionInfoPool->addConnectionInfo($key, $target, $info);
  }

  /**
   * Gets information on the specified database connection.
   *
   * @param string $key
   *   (optional) The connection key for which to return information.
   *
   * @return array[]|null
   *   Format: $[$target] = $info
   */
  public function getConnectionInfo($key = 'default') {
    return $this->connectionInfoPool->getConnectionInfo($key);
  }

  /**
   * Gets connection information for all available databases.
   *
   * @return array[][]
   */
  public function getAllConnectionInfo() {
    return $this->connectionInfoPool->getAllConnectionInfo();
  }

  /**
   * Sets connection information for multiple databases.
   *
   * @param array[][] $databases
   *   A multi-dimensional array specifying database connection parameters, as
   *   defined in settings.php.
   */
  public function setMultipleConnectionInfo(array $databases) {
    $this->connectionInfoPool->setMultipleConnectionInfo($databases);
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
  public function renameConnection($old_key, $new_key) {
    if ($this->connectionInfoPool->renameConnection($old_key, $new_key)) {
      // Migrate over the DatabaseConnection object if it exists.
      if (isset($this->connections[$old_key])) {
        $this->connections[$new_key] = $this->connections[$old_key];
        unset($this->connections[$old_key]);
      }

      return TRUE;
    }
    else {
      return FALSE;
    }
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
  public function removeConnection($key) {
    if ($this->connectionInfoPool->removeConnection($key)) {
      $this->closeConnection(NULL, $key);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Opens a connection to the server specified by the given key and target.
   *
   * @param string $key
   *   The database connection key, as specified in settings.php. The default is
   *   "default".
   * @param string $target
   *   The database target to open.
   *
   * @return \Drupal\Core\Database\Connection
   *   The newly opened database connection.
   *
   * @throws \Drupal\Core\Database\ConnectionNotDefinedException
   * @throws \Drupal\Core\Database\DriverNotSpecifiedException
   */
  protected function openConnection($key, $target) {
    $info = $this->connectionInfoPool->requireConnectionInfo($key, $target);

    $new_connection = $info->openConnection();
    $new_connection->setTarget($target);
    $new_connection->setKey($key);

    // If we have any active logging objects for this connection key, we need
    // to associate them with the connection we just opened.
    if (!empty($this->logs[$key])) {
      $new_connection->setLogger($this->logs[$key]);
    }

    return $new_connection;
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
  public function closeConnection($target = NULL, $key = NULL) {
    // Gets the active connection by default.
    if (!isset($key)) {
      $key = $this->activeKey;
    }
    // To close a connection, it needs to be set to NULL and removed from the
    // variable. In all cases, closeConnection() might be called for a
    // connection that was not opened yet, in which case the key is not defined
    // yet and we just ensure that the connection key is undefined.
    if (isset($target)) {
      if (isset($this->connections[$key][$target])) {
        $this->connections[$key][$target]->destroy();
        $this->connections[$key][$target] = NULL;
      }
      unset($this->connections[$key][$target]);
    }
    else {
      if (isset($this->connections[$key])) {
        foreach ($this->connections[$key] as $target => $connection) {
          $this->connections[$key][$target]->destroy();
          $this->connections[$key][$target] = NULL;
        }
      }
      unset($this->connections[$key]);
    }
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
  public function ignoreTarget($key, $target) {
    $this->ignoreTargets[$key][$target] = TRUE;
  }

}
