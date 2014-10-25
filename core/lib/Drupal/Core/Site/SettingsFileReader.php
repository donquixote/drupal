<?php


namespace Drupal\Core\Site;

use Drupal\Core\Database\Database;

/**
 * Reader for settings.php
 */
class SettingsFileReader {

  /**
   * @param string $site_path
   *   The current site path.
   * @param \Composer\Autoload\ClassLoader $class_loader
   *   The class loader that is used for this request. Passed by reference and
   *   exposed to the local scope of settings.php, so as to allow it to be
   *   decorated with Symfony's ApcClassLoader, for example.
   *
   * @return \Drupal\Core\Site\SiteSettings
   */
  public function readSettingsFile($site_path, &$class_loader) {

    // Declare some variables to be overwritten in settings.php.
    // Some of these will later be exported as globals.
    $base_url = NULL;
    $cookie_domain = NULL;
    $config_directories = NULL;
    $settings = array();
    $config = array();
    $databases = array();

    // Make $site_path available as local variable in settings.php.
    if (is_readable(DRUPAL_ROOT . '/' . $site_path . '/settings.php')) {
      require DRUPAL_ROOT . '/' . $site_path . '/settings.php';
    }

    return new SiteSettings(
      $settings,
      array(
        'base_url' => $base_url,
        'cookie_domain' => $cookie_domain,
        'config_directories' => $config_directories,
        'config' => $config,
        'databases' => $databases,
      ));
  }

}
