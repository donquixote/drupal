<?php


namespace Drupal\Core\Database\Manager;

/**
 * Exception that is thrown in static methods of
 * @see \Drupal\Core\Database\Database, if
 * @see \Drupal\Core\Database\Database;;setDatabaseManager() has not been called
 * yet.
 */
class NotInitializedException extends \Exception {

}
