<?php


namespace Drupal\Core\Database\Manager;

use Drupal\Core\Database\ConnectionNotDefinedException;

/**
 * Manages ConnectionInfo objects.
 */
class ConnectionInfoPool {

  /**
   * Processed database connection information from settings.php, wrapped in
   * ConnectionInfo objects.
   *
   * @var ConnectionInfo[][]
   *   Format: $[$key][$target] instanceof ConnectionInfo
   */
  protected $databaseInfo = array();

  /**
   * @param string $key
   * @param string $target
   *
   * @return bool
   */
  public function targetExists($key, $target) {
    return isset($this->databaseInfo[$key][$target]);
  }

  /**
   * @param string $key
   *
   * @return bool
   */
  public function keyExists($key) {
    return !empty($this->databaseInfo[$key]);
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
    if (empty($this->databaseInfo[$key][$target])) {
      $infoObject = ConnectionInfo::createFromInfoArray($info);
      $this->databaseInfo[$key][$target] = $infoObject;
    }
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
    if (empty($this->databaseInfo[$key])) {
      return NULL;
    }
    $targets = array();
    foreach ($this->databaseInfo[$key] as $target => $infoObject) {
      $targets[$target] = $infoObject->toArray();
    }
    return $targets;
  }

  /**
   * Gets connection information for all available databases.
   *
   * @return array[][]
   */
  public function getAllConnectionInfo() {
    $all = array();
    foreach ($this->databaseInfo as $key => $targets) {
      foreach ($targets as $target => $infoObject) {
        $all[$key][$target] = $infoObject->toArray();
      }
    }
    return $all;
  }

  /**
   * Sets connection information for multiple databases.
   *
   * @param array[][] $databases
   *   A multi-dimensional array specifying database connection parameters, as
   *   defined in settings.php.
   */
  public function setMultipleConnectionInfo(array $databases) {
    foreach ($databases as $key => $targets) {
      foreach ($targets as $target => $info) {
        $this->addConnectionInfo($key, $target, $info);
      }
    }
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
    if (!empty($this->databaseInfo[$old_key]) && empty($this->databaseInfo[$new_key])) {
      // Migrate the database connection information.
      $this->databaseInfo[$new_key] = $this->databaseInfo[$old_key];
      unset($this->databaseInfo[$old_key]);

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
    if (isset($this->databaseInfo[$key])) {
      unset($this->databaseInfo[$key]);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * @param string $key
   *   The database connection key, as specified in settings.php. The default is
   *   "default".
   * @param string $target
   *   The database target to open.
   *
   * @return \Drupal\Core\Database\Manager\ConnectionInfo
   *
   * @throws \Drupal\Core\Database\ConnectionNotDefinedException
   */
  public function requireConnectionInfo($key, $target) {

    if (isset($this->databaseInfo[$key][$target])) {
      return $this->databaseInfo[$key][$target];
    }

    if (!isset($this->databaseInfo[$key])) {
      throw new ConnectionNotDefinedException('The specified database connection is not defined: ' . $key);
    }

    throw new ConnectionNotDefinedException('The specified database connection target is not defined: ' . $key . ' / ' . $target);
  }

}
