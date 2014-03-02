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
   * @var SiteDirectory|null
   */
  private $siteDirectory;

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
   * @throws \BadMethodCallException
   * @see drupal_settings_initialize()
   */
  public static function init($root_directory, array $sites = NULL, $custom_path = NULL) {
    if (isset(self::$instance)) {
      // Only the installer environment is allowed to instantiate the Site
      // singleton prior to drupal_settings_initialize().
      // @see Site::initInstaller()
      if (!self::$instance->isInstaller) {
        throw new \BadMethodCallException('Site path is initialized already.');
      }
    }
    else {
      new self($root_directory);
    }
    self::$instance->initializePath($sites, $custom_path);

    // Prevent this method from being called more than once.
    if (self::$instance->isInstaller) {
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
   * @throws \BadMethodCallException
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
   *
   * @param string $root_directory
   *
   * @throws \BadMethodCallException
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
   * Initializes the site path.
   *
   * @param array|null $sites
   *   (optional) A multi-site mapping, as defined in settings.php,
   *   or NULL if no multi-site functionality is enabled.
   * @param string $custom_path
   *   (optional) An explicit site path to set; skipping site negotiation.
   */
  private function initializePath(array $sites = NULL, $custom_path = NULL) {
    // Force-override the site directory in tests.
    if ($test_prefix = drupal_valid_test_ua()) {
      $path = 'sites/simpletest/' . substr($test_prefix, 10);
    }
    // An explicitly defined $conf_path in /settings.php takes precedence.
    elseif (isset($custom_path)) {
      $path = $custom_path;
    }
    // If the multi-site functionality was enabled in /settings.php, discover
    // the path for the current site.
    // $sites just needs to be defined; an explicit mapping is not required.
    elseif (isset($sites)) {
      $site_picker = new SitePicker($this->root, $sites);
      $path = $site_picker->determinePath(!$this->isInstaller);
    }
    // If the multi-site functionality is not enabled, the Drupal root
    // directory is the site directory.
    else {
      $path = '';
    }
    $this->siteDirectory = new SiteDirectory($path);
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
   * @throws \RuntimeException
   * @return string
   *   The prefixed filepath.
   */
  private function resolvePath($filepath) {
    // Extra safety protection in case a script somehow manages to bypass all
    // other protections.
    if (!isset($this->siteDirectory)) {
      throw new \RuntimeException('Site path is not initialized yet.');
    }
    return $this->siteDirectory->resolvePath($filepath);
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
