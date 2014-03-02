<?php

/**
 * @file
 * Contains \Drupal\Core\Site\Site.
 */

namespace Drupal\Core\Site;

/**
 * A utility class for easy access to the site path.
 */
class Site {

  /**
   * The absolute path to the Drupal root directory.
   *
   * @var string
   */
  private $root;

  /**
   * The relative path to the site directory.
   *
   * May be an empty string, in case the site directory is the root directory.
   *
   * @var string
   */
  private $path;

  /**
   * Whether the Site singleton was instantiated by the installer.
   *
   * @var bool
   */
  private $isInstaller = FALSE;

  /**
   * The original Site instance of the test runner during test execution.
   *
   * @see \Drupal\Core\Site\Site::setUpTest()
   * @see \Drupal\Core\Site\Site::tearDownTest()
   *
   * @var \Drupal\Core\Site\Site
   */
  private static $original;

  /**
   * The Site singleton instance.
   *
   * @var \Drupal\Core\Site\Site
   */
  private static $instance;

  /**
   * Initializes the Site singleton.
   *
   * @param string $root_directory
   *   The root directory to use for absolute paths; i.e., DRUPAL_ROOT.
   * @param array $sites
   *   (optional) A multi-site mapping, as defined in settings.php.
   * @param string $custom_path
   *   (optional) An explicit site path to set; skipping site negotiation.
   *   This can be defined as $conf_path in the root /settings.php file.
   *
   * @see drupal_settings_initialize()
   */
  public static function init($root_directory, array $sites = NULL, $custom_path = NULL) {
    if (isset(self::$instance)) {
      // Only the installer environment is allowed to instantiate the Site
      // singleton prior to drupal_settings_initialize().
      // @see Site::initInstaller()
      if (!self::$instance->isInstaller()) {
        throw new \BadMethodCallException('Site path is initialized already.');
      }
    }
    else {
      new self($root_directory);
    }
    self::$instance->initializePath($sites, $custom_path);

    // Prevent this method from being called more than once.
    if (self::$instance->isInstaller()) {
      self::$instance->isInstaller = FALSE;
    }
  }

  /**
   * Initializes the Site singleton for the early installer environment.
   *
   * The installer uses this function to prime the site directory path very
   * early in the installer environment. This allows the application to be
   * installed into a new and empty site directory, which does not contain a
   * settings.php yet.
   *
   * @param string $root_directory
   *   The root directory to use for absolute paths; i.e., DRUPAL_ROOT.
   *
   * @see install_begin_request()
   */
  public static function initInstaller($root_directory) {
    if (isset(self::$instance)) {
      throw new \BadMethodCallException('Site path is initialized already.');
    }
    new self($root_directory);
    // Denote that we are operating in the special installer environment.
    self::$instance->isInstaller = TRUE;
  }

  /**
   * Constructs the Site singleton.
   */
  private function __construct($root_directory) {
    if (isset(self::$instance)) {
      throw new \BadMethodCallException('Site path is initialized already.');
    }
    $this->root = $root_directory;
    self::$instance = $this;
  }

  /**
   * Re-initializes (resets) the Site singleton for a test run.
   *
   * @see \Drupal\simpletest\TestBase::prepareEnvironment()
   */
  public static function setUpTest() {
    if (!isset(self::$instance)) {
      throw new \RuntimeException('No original Site to backup. Missing invocation of Site::init()?');
    }
    if (!drupal_valid_test_ua()) {
      throw new \BadMethodCallException('Site is not executing a test.');
    }
    self::$original = clone self::$instance;
    self::$instance = NULL;
  }

  /**
   * Reverts the Site singleton to the original after a test run.
   *
   * @see \Drupal\simpletest\TestBase::restoreEnvironment()
   */
  public static function tearDownTest() {
    if (!isset(self::$original)) {
      throw new \RuntimeException('No original Site to revert to. Missing invocation of Site::setUpTest()?');
    }
    // Do not allow to restore original Site singleton in a test environment,
    // unless we are testing the test environment setup and teardown itself.
    // @see \Drupal\simpletest\Tests\BrokenSetUpTest
    if (drupal_valid_test_ua() && !DRUPAL_TEST_IN_CHILD_SITE) {
      throw new \BadMethodCallException('Unable to revert Site: A test is still being executed.');
    }
    self::$instance = clone self::$original;
    self::$original = NULL;
  }

  /**
   * Returns whether the Site singleton was instantiated for the installer.
   */
  private function isInstaller() {
    return $this->isInstaller;
  }

  /**
   * Initializes the site path.
   *
   * @param array $sites
   *   (optional) A multi-site mapping, as defined in settings.php.
   * @param string $custom_path
   *   (optional) An explicit site path to set; skipping site negotiation.
   */
  private function initializePath(array $sites = NULL, $custom_path = NULL) {
    // Force-override the site directory in tests.
    if ($test_prefix = drupal_valid_test_ua()) {
      $this->path = 'sites/simpletest/' . substr($test_prefix, 10);
    }
    // An explicitly defined $conf_path in /settings.php takes precedence.
    elseif (isset($custom_path)) {
      $this->path = $custom_path;
    }
    // If the multi-site functionality was enabled in /settings.php, discover
    // the path for the current site.
    // $sites just needs to be defined; an explicit mapping is not required.
    elseif (isset($sites)) {
      $this->path = $this->determinePath($sites, !$this->isInstaller());
    }
    // If the multi-site functionality is not enabled, the Drupal root
    // directory is the site directory.
    else {
      $this->path = '';
    }
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
   * @param array $sites
   *   A multi-site mapping, as defined in settings.php.
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
  private function determinePath(array $sites, $require_settings) {
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
        if (isset($sites[$dir])) {
          $dir = $sites[$dir];
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

  /**
   * Prefixes a given filepath with the site directory, if any.
   *
   * Site::getPath() and this helper method only exists to ensure that a given
   * filepath does not result in an absolute filesystem path in case of a string
   * concatenation like the following:
   *
   * @code
   * // If the sire directory path is empty (root directory), then the resulting
   * // filesystem path would become absolute; i.e.: "/some/file"
   * unlink($site_path . '/some/file');
   * @endcode
   *
   * In case the PHP process has write access to the entire filesystem, such a
   * file operation could succeed and potentially affect arbitrary other files
   * and directories that happen to exist. That must not happen.
   *
   * @param string $filepath
   *   The filepath to prefix.
   *
   * @return string
   *   The prefixed filepath.
   */
  private function resolvePath($filepath) {
    // Extra safety protection in case a script somehow manages to bypass all
    // other protections.
    if (!isset($this->path)) {
      throw new \RuntimeException('Site path is not initialized yet.');
    }
    // A faulty call to Site::getPath() might include a leading slash (/), in
    // which case the entire site path resolution of this function would be
    // pointless, because the resulting path would still be absolute. Therefore,
    // guarantee that even a bogus argument is resolved correctly.
    $filepath = ltrim($filepath, '/');

    if ($this->path !== '') {
      if ($filepath !== '') {
        return $this->path . '/' . $filepath;
      }
      return $this->path;
    }
    return $filepath;
  }

  /**
   * Returns a given path as relative path to the site directory.
   *
   * Use this function instead of appending strings to the site path manually,
   * because the site directory may be the root directory and thus the resulting
   * path would be an absolute filesystem path.
   *
   * @param string $filepath
   *   (optional) A relative filepath to append to the site path.
   *
   * @return string
   *   The given $filepath, potentially prefixed with the site path.
   *
   * @see \Drupal\Core\Site\Site::getAbsolutePath()
   */
  public static function getPath($filepath = '') {
    return self::$instance->resolvePath($filepath);
  }

  /**
   * Returns a given path as absolute path in the site directory.
   *
   * @param string $filepath
   *   (optional) A relative filepath to append to the site path.
   *
   * @return string
   *   The given $filepath, potentially prefixed with the site path, as an
   *   absolute filesystem path.
   *
   * @see \Drupal\Core\Site\Site::getPath()
   */
  public static function getAbsolutePath($filepath = '') {
    $filepath = self::$instance->resolvePath($filepath);
    if ($filepath !== '') {
      return self::$instance->root . '/' . $filepath;
    }
    else {
      return self::$instance->root;
    }
  }

}
