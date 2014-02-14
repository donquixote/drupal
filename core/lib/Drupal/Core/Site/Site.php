<?php

/**
 * @file
 * Contains \Drupal\Core\Site\Site.
 */

namespace Drupal\Core\Site;

/**
 * A utility class for easy access to the site path.
 */
abstract class Site {

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
   * @var \Drupal\Core\Site\SiteInstance
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
    // Only the installer environment is allowed instantiate the Site singleton
    // prior to drupal_settings_initialize().
    // @see initInstaller()
    if (isset(self::$instance)) {
      if (!self::$instance->isInstaller()) {
        throw new \BadMethodCallException('Site path is initialized already.');
      }
      else {
        // Disable the $isInstaller flag to prevent init() from being invoked
        // more than once.
        self::$instance->setIsInstaller(FALSE);
      }
    }
    else {
      self::initInstance($root_directory);
    }
    self::$instance->initializePath($sites, $custom_path);
  }

  /**
   * Initializes the Site singleton for the early installer environment.
   *
   * The installer uses this function to prime the site directory path very
   * early in the installer environmnt. This allows the application to be
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
    // Set a global state flag to denote that we are operating in the special
    // installer environment.
    self::initInstance($root_directory, TRUE);
    self::$instance->initializePath();
  }

  /**
   * @param $root_directory
   * @param bool $is_installer
   *
   * @throws \BadMethodCallException
   */
  private static function initInstance($root_directory, $is_installer = FALSE) {
    if (isset(self::$instance)) {
      throw new \BadMethodCallException('Site path is initialized already.');
    }
    self::$instance = new SiteInstance($root_directory, $is_installer);
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
    return self::$instance->getAbsolutePath($filepath);
  }

}
