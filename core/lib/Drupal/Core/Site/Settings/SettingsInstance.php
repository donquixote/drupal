<?php


namespace Drupal\Core\Site\Settings;

use Drupal\Core\Site\Settings\SettingsInterface;

/**
 * Object to encapsulate the $settings array from settings.php.
 *
 * @see \Drupal\Core\Site\Settings
 */
class SettingsInstance implements SettingsInterface {

  /**
   * Array with the settings.
   *
   * @var array
   */
  private $storage = array();

  /**
   * Constructor.
   *
   * @param array $settings
   *   Array with the settings.
   */
  public function __construct(array $settings) {
    $this->storage = $settings;
  }

  /**
   * Returns a setting.
   *
   * Settings can be set in settings.php in the $settings array and requested
   * by this function. Settings should be used over configuration for read-only,
   * possibly low bootstrap configuration that is environment specific.
   *
   * @param string $name
   *   The name of the setting to return.
   * @param mixed $default
   *   (optional) The default value to use if this setting is not set.
   *
   * @return mixed
   *   The value of the setting, the provided default if not set.
   */
  public function get($name, $default = NULL) {
    return isset($this->storage[$name])
      ? $this->storage[$name]
      : $default;
  }

  /**
   * Returns all the settings. This is only used for testing purposes.
   *
   * @return array
   *   All the settings.
   */
  public function getAll() {
    return $this->storage;
  }

  /**
   * Gets a salt useful for hardening against SQL injection.
   *
   * @return string
   *   A salt based on information in settings.php, not in the database.
   *
   * @throws \RuntimeException
   */
  public function getHashSalt() {
    $hash_salt = $this->get('hash_salt');
    // This should never happen, as it breaks user logins and many other
    // services. Therefore, explicitly notify the user (developer) by throwing
    // an exception.
    if (empty($hash_salt)) {
      throw new \RuntimeException('Missing $settings[\'hash_salt\'] in settings.php.');
    }

    return $hash_salt;
  }
}
