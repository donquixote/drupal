<?php


namespace Drupal\Core\Site;

use Drupal\Core\Database\Database;

/**
 * Reader for settings.php
 */
class SettingsFileReader {

  /**
   * @param string $file
   *
   * @return \Drupal\Core\Site\SiteSettings
   */
  public function readFromFile($file) {
    // Export these settings.php variables to the global namespace.
    global $base_url, $cookie_domain, $config_directories, $config;
    $settings = array();
    $config = array();
    $databases = array();

    // Make conf_path() available as local variable in settings.php.
    if (is_readable($file)) {
      require $file;
    }

    // Initialize Database.
    Database::setMultipleConnectionInfo($databases);

    // Initialize Settings.
    return new SiteSettings($settings);
  }

}
