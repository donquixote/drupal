<?php

/**
 * @file
 * Contains \Drupal\Core\Site\Settings.
 */

namespace Drupal\Core\Site;

use Drupal\Core\Database\Database;
use Drupal\Core\Site\Settings\PlaceholderSettings;
use Drupal\Core\Site\Settings\SettingsInstance;
use Drupal\Core\Site\Settings\SettingsInterface;

/**
 * Initialize Settings::$instance with a placeholder object.
 * See https://www.drupal.org/node/2363881
 *
 * This technique is the most reliable way to initialize static properties with
 * non-trivial expressions. It should NOT be used for anything else. Also, the
 * code being called MUST NOT have any side effects other than initializing the
 * static properties.
 *
 * In general (e.g. in PSR-1), a PHP file should either declare symbols OR have
 * side-effects, but not both. This specific case is ok only because the side
 * effect applies to nothing else but the class declared in the same file, and
 * it happens immediately after the class is being declared. A version of the
 * class without this initialization applied is never available to the outside
 * world.
 *
 * Note: PHP does not care whether this is called before or after the class
 * declaration. It is called before only for better visibility.
 */
Settings::initStaticProperties();

/**
 * Read only settings that are initialized with the class.
 *
 * @ingroup utility
 */
abstract class Settings {

  /**
   * Singleton instance.
   *
   * @var \Drupal\Core\Site\Settings\SettingsInterface
   */
  protected static $instance;

  /**
   * Initializes the static properties. Called once from within the class file.
   */
  public static function initStaticProperties() {
    static::$instance = new PlaceholderSettings('Settings::$instance is not initialized yet.');
  }

  /**
   * Sets or replaces static::$instance with a new Settings object created from a
   * settings array.
   *
   * @param array $settings
   *   Array with the settings.
   *
   * @return \Drupal\Core\Site\Settings\SettingsInterface
   *   The new value of static::$instance.
   */
  public static function setCreateInstance(array $settings) {
    static::$instance = new SettingsInstance($settings);
    return static::$instance;
  }

  /**
   * Sets or replaces static::$instance with the given Settings object.
   *
   * @param \Drupal\Core\Site\Settings\SettingsInterface $instance
   */
  public static function setInstance(SettingsInterface $instance) {
    static::$instance = $instance;
  }

  /**
   * Returns the settings instance.
   *
   * A singleton is used because this class is used before the container is
   * available.
   *
   * @return \Drupal\Core\Site\Settings\SettingsInterface|null
   */
  public static function getInstance() {
    if (static::$instance instanceof PlaceholderSettings) {
      return NULL;
    }
    return static::$instance;
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
  public static function get($name, $default = NULL) {
    return static::$instance->get($name, $default);
  }

  /**
   * Returns all the settings. This is only used for testing purposes.
   *
   * @return array
   *   All the settings.
   */
  public static function getAll() {
    return static::$instance->getAll();
  }

  /**
   * Bootstraps settings.php and the Settings singleton.
   *
   * @param string $site_path
   *   The current site path.
   * @param \Composer\Autoload\ClassLoader $class_loader
   *   The class loader that is used for this request. Passed by reference and
   *   exposed to the local scope of settings.php, so as to allow it to be
   *   decorated with Symfony's ApcClassLoader, for example.
   *
   * @see default.settings.php
   */
  public static function initialize($site_path, &$class_loader) {
    // Export these settings.php variables to the global namespace.
    global $base_url, $cookie_domain, $config_directories, $config;
    $settings = array();
    $config = array();
    $databases = array();

    // Make conf_path() available as local variable in settings.php.
    if (is_readable(DRUPAL_ROOT . '/' . $site_path . '/settings.php')) {
      require DRUPAL_ROOT . '/' . $site_path . '/settings.php';
    }

    // Initialize Database.
    Database::setMultipleConnectionInfo($databases);

    // Initialize Settings.
    static::setCreateInstance($settings);
  }

  /**
   * Gets a salt useful for hardening against SQL injection.
   *
   * @return string
   *   A salt based on information in settings.php, not in the database.
   *
   * @throws \RuntimeException
   */
  public static function getHashSalt() {
    return static::$instance->getHashSalt();
  }

}
