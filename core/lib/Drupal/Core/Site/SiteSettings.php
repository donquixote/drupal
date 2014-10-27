<?php


namespace Drupal\Core\Site;

use Drupal\Component\Utility\String;
use Drupal\Core\Site\Settings\SettingsInstance;

/**
 * @property string|null baseUrl
 *   The $base_url value from settings.php
 * @property string|null cookieDomain
 *   The $cookie_domain value from settings.php
 * @property string[]|null configDirectories
 *   The $config_directories array from settings.php
 * @property array config
 *   The $config array from settings.php
 * @property array[][] databases
 *   The $databases array from settings.php.
 * @property \Drupal\Core\Site\Settings\SettingsInterface settings
 *   Object encapsulating the $settings array from settings.php.
 */
class SiteSettings {

  /**
   * Array with the settings.
   *
   * @var mixed[]
   */
  private $values;

  /**
   * @var \Composer\Autoload\ClassLoader|null
   */
  private $classLoader;

  /**
   * Constructor.
   *
   * @param mixed[] $values
   *   Array with the variables from settings.php.
   */
  public function __construct(array $values) {

    $values += array(
      'base_url' => NULL,
      'cookie_domain' => NULL,
      'config_directories' => array(),
      'config' => array(),
      'databases' => array(),
      'settings' => array(),
      'class_loader' => NULL,
    );

    // @todo Validate all values, and throw \InvalidArgumentException if invalid.

    $this->values = array(
      'baseUrl' => $values['base_url'],
      'cookieDomain' => $values['cookie_domain'],
      'configDirectories' => $values['config_directories'],
      'config' => $values['config'],
      'databases' => $values['databases'],
      'settings' => new SettingsInstance($values['settings']),
    );

    $this->classLoader = $values['class_loader'];
  }

  /**
   * Magic __get() method. This allows to take advantage of "@property" in the
   * class docblock.
   *
   * @param string $key
   *
   * @return mixed
   * @throws \InvalidArgumentException
   */
  public function __get($key) {
    if (!array_key_exists($key, $this->values)) {
      throw new \InvalidArgumentException(
        String::format("Settings key '@key' does not exist.", array('@key' => $key)));
    }
    return $this->values[$key];
  }

  /**
   * Exports some of the settings to global values.
   */
  public function exportGlobals() {
    $GLOBALS['base_url'] = $this->values['baseUrl'];
    $GLOBALS['cookie_domain'] = $this->values['cookieDomain'];
    $GLOBALS['config_directories'] = $this->values['configDirectories'];
    $GLOBALS['config'] = $this->values['config'];
  }

}
