<?php
namespace Drupal\Core\Site\Settings;

/**
 * Read only settings that are initialized with the class.
 *
 * @ingroup utility
 */
interface SettingsInterface {

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
  public function get($name, $default = NULL);

  /**
   * Returns all the settings. This is only used for testing purposes.
   *
   * @return array
   *   All the settings.
   */
  public function getAll();

  /**
   * Gets a salt useful for hardening against SQL injection.
   *
   * @return string
   *   A salt based on information in settings.php, not in the database.
   *
   * @throws \RuntimeException
   */
  public function getHashSalt();

}
