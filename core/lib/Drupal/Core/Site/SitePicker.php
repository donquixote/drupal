<?php


namespace Drupal\Core\Site;

/**
 * Utility class to choose a site directory based on the current environment.
 */
class SitePicker {

  /**
   * The Drupal root directory.
   *
   * @var string
   */
  private $root;

  /**
   * An array of available sites.
   *
   * @var array
   */
  private $sites;

  /**
   * @param string $root
   * @param array $sites
   *   A multi-site mapping, as defined in settings.php.
   */
  public function __construct($root, array $sites) {
    $this->root = $root;
    $this->sites = $sites;
  }

  /**
   * Finds the appropriate configuration directory for a given host and path.
   *
   * Finds a matching configuration directory file by stripping the website's
   * hostname from left to right and pathname from right to left. By default,
   * the directory must contain a 'settings.php' file for it to match. If the
   * parameter $require_settings is set to FALSE, then a directory without a
   * 'settings.php' file will match as well. The first configuration
   * file found will be used and the remaining ones will be ignored.
   *
   * The settings.php file can define aliases in an associative array named
   * $sites. For example, to create a directory alias for
   * http://www.drupal.org:8080/mysite/test whose configuration file is in
   * sites/example.com, the array should be defined as:
   * @code
   * $sites = array(
   *   '8080.www.drupal.org.mysite.test' => 'example.com',
   * );
   * @endcode
   *
   * @see default.settings.php
   *
   * @param bool $require_settings
   *   Only configuration directories with an existing settings.php file
   *   will be recognized. Defaults to TRUE. During initial installation,
   *   this is set to FALSE so that Drupal can detect a matching directory,
   *   then create a new settings.php file in it.
   *
   * @return string
   *   The path of the matching configuration directory. May be an empty string,
   *   in case the site configuration directory is the root directory.
   */
  public function determinePath($require_settings) {
    // The hostname and optional port number, e.g. "www.example.com" or
    // "www.example.com:8080".
    $http_host = $_SERVER['HTTP_HOST'];
    // The part of the URL following the hostname, including the leading slash.
    $script_name = $_SERVER['SCRIPT_NAME'] ?: $_SERVER['SCRIPT_FILENAME'];

    $uri = explode('/', $script_name);
    $server = explode('.', implode('.', array_reverse(explode(':', rtrim($http_host, '.')))));
    for ($i = count($uri) - 1; $i > 0; $i--) {
      for ($j = count($server); $j > 0; $j--) {
        $dir = implode('.', array_slice($server, -$j)) . implode('.', array_slice($uri, 0, $i));
        // Check for an alias in $sites from settings.php.
        if (isset($this->sites[$dir])) {
          $dir = $this->sites[$dir];
        }
        if ($require_settings) {
          if (file_exists($this->root . '/sites/' . $dir . '/settings.php')) {
            return "sites/$dir";
          }
        }
        elseif (file_exists($this->root . '/sites/' . $dir)) {
          return "sites/$dir";
        }
      }
    }
    return '';
  }

} 
