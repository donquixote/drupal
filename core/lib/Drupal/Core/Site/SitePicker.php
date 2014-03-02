<?php


namespace Drupal\Core\Site;

/**
 * Utility class to choose a site directory based on the current environment.
 */
class SitePicker {

  /**
   * @var array
   */
  private $uriFragments;

  /**
   * Reverse domain fragments, e.g.
   *
   * @var array
   */
  private $reverseServerFragments;

  /**
   * @return self
   */
  public static function createFromEnvironment() {
    $http_host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'] ?: $_SERVER['SCRIPT_FILENAME'];
    return new self($http_host, $script_name);
  }

  /**
   * @param string $http_host
   *   The hostname and optional port number, e.g. "www.example.com" or
   *   "www.example.com:8080".
   * @param string $script_name
   *   The part of the URL following the hostname, including the leading slash.
   */
  public function __construct($http_host, $script_name) {
    $this->uriFragments = explode('/', $script_name);
    $this->reverseServerFragments = explode('.', implode('.', array_reverse(explode(':', rtrim($http_host, '.')))));
  }

  /**
   * Finds the site path in a multisite scenario.
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
   * @param string $root
   * @param array|null $sites
   *   (optional) A multi-site mapping, as defined in settings.php,
   *   or NULL if no multi-site functionality is enabled.
   * @param bool $require_settings_file
   *   If TRUE, directories that don't have a settings.php will be skipped
   *   during discovery.
   *
   * @return string
   *   The path of the matching configuration directory. May be an empty string,
   *   in case the site configuration directory is the root directory.
   */
  public function discoverPath($root, $sites, $require_settings_file) {

    for ($i = count($this->uriFragments) - 1; $i > 0; $i--) {
      for ($j = count($this->reverseServerFragments); $j > 0; $j--) {
        $dir = implode('.', array_slice($this->reverseServerFragments, -$j))
          . implode('.', array_slice($this->uriFragments, 0, $i));
        // Check for an alias in $sites from settings.php.
        if (isset($sites[$dir])) {
          $dir = $sites[$dir];
        }
        if ($require_settings_file) {
          if (file_exists($root . '/sites/' . $dir . '/settings.php')) {
            return "sites/$dir";
          }
        }
        elseif (file_exists($root . '/sites/' . $dir)) {
          return "sites/$dir";
        }
      }
    }
    return '';
  }

} 
