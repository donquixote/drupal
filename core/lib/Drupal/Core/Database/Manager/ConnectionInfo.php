<?php


namespace Drupal\Core\Database\Manager;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\DriverNotSpecifiedException;

/**
 * Encapsulates connection information for a single database.
 */
class ConnectionInfo {

  /**
   * @var array
   */
  private $info;

  /**
   * @param array $info
   *
   * @return self
   */
  public static function createFromInfoArray(array $info) {
    $info = Database::parseConnectionInfo($info);
    return new self($info);
  }

  /**
   * @param array $info
   *   Normalized $info array.
   */
  public function __construct($info) {
    $this->info = $info;
  }

  /**
   * @return array
   */
  public function toArray() {
    return $this->info;
  }

  /**
   * @return \Drupal\Core\Database\Connection
   */
  public function openConnection() {
    $driver_class = $this->getDriverClass();

    /** @var \Drupal\Core\Database\Connection $driver_class */
    $pdo_connection = $driver_class::open($this->info);

    return new $driver_class($pdo_connection, $this->info);
  }

  /**
   * @return string
   */
  public function getDriverClass() {

    if (!empty($this->info['namespace'])) {
      return $this->info['namespace'] . '\\Connection';
    }

    if (!empty($this->info['driver'])) {
      return 'Drupal\Core\Database\Driver\\' . $this->info['driver'] . '\Connection';
    }

    throw new DriverNotSpecifiedException('Driver not specified for this database connection.');
  }

}
