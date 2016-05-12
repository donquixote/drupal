<?php

namespace Drupal\Core\Extension;

use Drupal\Component\FileCache\FileCacheFactory;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface;
use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedSingleton;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

/**
 * Discovers available extensions in the filesystem.
 *
 * To also discover test modules, add
 * @code
 * $settings['extension_discovery_scan_tests'] = TRUE;
 * @encode
 * to your settings.php.
 *
 */
class ExtensionDiscovery {

  /**
   * Origin directory weight: Core.
   */
  const ORIGIN_CORE = 0;

  /**
   * Origin directory weight: Installation profile.
   */
  const ORIGIN_PROFILE = 1;

  /**
   * Origin directory weight: sites/all.
   */
  const ORIGIN_SITES_ALL = 2;

  /**
   * Origin directory weight: Site-wide directory.
   */
  const ORIGIN_ROOT = 3;

  /**
   * Origin directory weight: Parent site directory of a test site environment.
   */
  const ORIGIN_PARENT_SITE = 4;

  /**
   * Origin directory weight: Site-specific directory.
   */
  const ORIGIN_SITE = 5;

  /**
   * Regular expression to match PHP function names.
   *
   * @see http://php.net/manual/functions.user-defined.php
   */
  const PHP_FUNCTION_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

  /**
   * InfoParser instance for parsing .info.yml files.
   *
   * @var \Drupal\Core\Extension\InfoParser
   */
  protected $infoParser;

  /**
   * List of installation profile directories to additionally scan.
   *
   * @var string[]|null
   */
  protected $profileDirectories;

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * The file cache object.
   *
   * @var \Drupal\Component\FileCache\FileCacheInterface
   */
  protected $fileCache;

  /**
   * The site path.
   *
   * @var string
   */
  protected $sitePath;

  /**
   * The component that searches the directories, or NULL if not initialized.
   *
   * @var \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface|null
   */
  protected $searchdirToRawExtensionsGrouped;

  /**
   * Resets the static cache.
   */
  static function staticReset() {
    SearchdirToRawExtensionsGroupedSingleton::resetInstancesData();
  }

  /**
   * Constructs a new ExtensionDiscovery object.
   *
   * @param string $root
   *   The app root.
   * @param bool $use_file_cache
   *   Whether file cache should be used.
   * @param string[] $profile_directories
   *   The available profile directories
   * @param string $site_path
   *   The path to the site.
   */
  public function __construct($root, $use_file_cache = TRUE, $profile_directories = NULL, $site_path = NULL) {
    $this->root = $root;
    $this->fileCache = $use_file_cache ? FileCacheFactory::get('extension_discovery') : NULL;
    $this->profileDirectories = $profile_directories;
    $this->sitePath = $site_path;
  }

  /**
   * Sets the component that searches the directories.
   *
   * This allows to specify a custom component for this, instead of using
   * the default component.
   *
   * @param \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped
   *
   * @return $this
   */
  public function setSearchdirToRawExtensionsGrouped(SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped) {
    $this->searchdirToRawExtensionsGrouped = $searchdirToRawExtensionsGrouped;
    return $this;
  }

  /**
   * Discovers available extensions of a given type.
   *
   * Finds all extensions (modules, themes, etc) that exist on the site. It
   * searches in several locations. For instance, to discover all available
   * modules:
   * @code
   * $listing = new ExtensionDiscovery(\Drupal::root());
   * $modules = $listing->scan('module');
   * @endcode
   *
   * The following directories will be searched (in the order stated):
   * - the core directory; i.e., /core
   * - the installation profile directory; e.g., /core/profiles/standard
   * - the legacy site-wide directory; i.e., /sites/all
   * - the site-wide directory; i.e., /
   * - the site-specific directory; e.g., /sites/example.com
   *
   * To also find test modules, add
   * @code
   * $settings['extension_discovery_scan_tests'] = TRUE;
   * @encode
   * to your settings.php.
   *
   * The information is returned in an associative array, keyed by the extension
   * name (without .info.yml extension). Extensions found later in the search
   * will take precedence over extensions found earlier - unless they are not
   * compatible with the current version of Drupal core.
   *
   * @param string $type
   *   The extension type to search for. One of 'profile', 'module', 'theme', or
   *   'theme_engine'.
   * @param bool $include_tests
   *   (optional) Whether to explicitly include or exclude test extensions. By
   *   default, test extensions are only discovered when in a test environment.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   An associative array of Extension objects, keyed by extension name.
   */
  public function scan($type, $include_tests = NULL) {
    // Determine the installation profile directories to scan for extensions,
    // unless explicit profile directories have been set. Exclude profiles as we
    // cannot have profiles within profiles.
    if ($this->profileDirectories === NULL && $type !== 'profile') {
      $this->setProfileDirectoriesFromSettings();
    }

    // Search the core directory.
    $searchdirs[static::ORIGIN_CORE] = 'core';

    // Search the legacy sites/all directory.
    $searchdirs[static::ORIGIN_SITES_ALL] = 'sites/all';

    // Search for contributed and custom extensions in top-level directories.
    // The scan uses a whitelist to limit recursion to the expected extension
    // type specific directory names only.
    $searchdirs[static::ORIGIN_ROOT] = '';

    // Simpletest uses the regular built-in multi-site functionality of Drupal
    // for running web tests. As a consequence, extensions of the parent site
    // located in a different site-specific directory are not discovered in a
    // test site environment, because the site directories are not the same.
    // Therefore, add the site directory of the parent site to the search paths,
    // so that contained extensions are still discovered.
    // @see \Drupal\simpletest\WebTestBase::setUp()
    if ($parent_site = Settings::get('test_parent_site')) {
      $searchdirs[static::ORIGIN_PARENT_SITE] = $parent_site;
    }

    // Find the site-specific directory to search. Since we are using this
    // method to discover extensions including profiles, we might be doing this
    // at install time. Therefore Kernel service is not always available, but is
    // preferred.
    if (\Drupal::hasService('kernel')) {
      $searchdirs[static::ORIGIN_SITE] = \Drupal::service('site.path');
    }
    else {
      $searchdirs[static::ORIGIN_SITE] = $this->sitePath ?: DrupalKernel::findSitePath(Request::createFromGlobals());
    }

    // Unless an explicit value has been passed, manually check whether we are
    // in a test environment, in which case test extensions must be included.
    // Test extensions can also be included for debugging purposes by setting a
    // variable in settings.php.
    if (!isset($include_tests)) {
      $include_tests = Settings::get('extension_discovery_scan_tests') || drupal_valid_test_ua();
    }

    $searchdirToRawExtensionsGrouped = $this->searchdirToRawExtensionsGrouped !== NULL
      ? $this->searchdirToRawExtensionsGrouped
      : SearchdirToRawExtensionsGroupedSingleton::getInstance($this->root, $include_tests);

    $files = array();
    foreach ($searchdirs as $dir) {
      /** @var \Drupal\Core\Extension\Extension[][][] $raw_extensions_grouped */
      $raw_extensions_grouped = $searchdirToRawExtensionsGrouped->getRawExtensionsGrouped($dir === '' ? $dir : $dir . '/');
      if (array_key_exists($type, $raw_extensions_grouped)) {
        foreach ($raw_extensions_grouped[$type] as $subdir_name => $subdir_raw_extensions_by_name) {
          $files += $subdir_raw_extensions_by_name;
        }
      }
    }

    // If applicable, filter out extensions that do not belong to the current
    // installation profiles.
    $files = $this->filterByProfileDirectories($files);
    // Sort the discovered extensions by their originating directories.
    $origin_weights = array_flip($searchdirs);
    $files = $this->sort($files, $origin_weights);

    // Process and return the list of extensions keyed by extension name.
    return $this->process($files);
  }

  /**
   * Sets installation profile directories based on current site settings.
   *
   * @return $this
   */
  public function setProfileDirectoriesFromSettings() {
    $this->profileDirectories = array();
    $profile = drupal_get_profile();
    // For SimpleTest to be able to test modules packaged together with a
    // distribution we need to include the profile of the parent site (in
    // which test runs are triggered).
    if (drupal_valid_test_ua() && !drupal_installation_attempted()) {
      $testing_profile = \Drupal::config('simpletest.settings')->get('parent_profile');
      if ($testing_profile && $testing_profile !== $profile) {
        $this->profileDirectories[] = drupal_get_path('profile', $testing_profile);
      }
    }
    // In case both profile directories contain the same extension, the actual
    // profile always has precedence.
    if ($profile) {
      $this->profileDirectories[] = drupal_get_path('profile', $profile);
    }
    return $this;
  }

  /**
   * Gets the installation profile directories to be scanned.
   *
   * @return string[]|null
   *   A list of installation profile directory paths relative to the system
   *   root directory.
   */
  public function getProfileDirectories() {
    return $this->profileDirectories;
  }

  /**
   * Sets explicit profile directories to scan.
   *
   * @param string[] $paths
   *   A list of installation profile directory paths relative to the system
   *   root directory (without trailing slash) to search for extensions.
   *
   * @return $this
   */
  public function setProfileDirectories(array $paths = NULL) {
    $this->profileDirectories = $paths;
    return $this;
  }

  /**
   * Filters out extensions not belonging to the scanned installation profiles.
   *
   * @param \Drupal\Core\Extension\Extension[] $all_files.
   *   The list of all extensions.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   The filtered list of extensions.
   */
  protected function filterByProfileDirectories(array $all_files) {
    if ($this->profileDirectories === NULL || $this->profileDirectories === []) {
      return $all_files;
    }

    $all_files = array_filter($all_files, function (Extension $file) {
      if (strpos($file->subpath, 'profiles') !== 0) {
        // This extension doesn't belong to a profile, ignore it.
        return TRUE;
      }

      foreach ($this->profileDirectories as $weight => $profile_path) {
        if (strpos($file->getPath(), $profile_path) === 0) {
          // Parent profile found.
          return TRUE;
        }
      }

      return FALSE;
    });

    return $all_files;
  }

  /**
   * Sorts the discovered extensions.
   *
   * @param \Drupal\Core\Extension\Extension[] $all_files.
   *   The list of all extensions.
   * @param array $weights
   *   An array of weights, keyed by originating directory.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   The sorted list of extensions.
   */
  protected function sort(array $all_files, array $weights) {
    $origins = array();
    $profiles = array();
    foreach ($all_files as $key => $file) {
      // If the extension does not belong to a profile, just apply the weight
      // of the originating directory.
      if (strpos($file->subpath, 'profiles') !== 0) {
        $origins[$key] = $weights[$file->origin];
        $profiles[$key] = NULL;
      }
      // If the extension belongs to a profile but no profile directories are
      // defined, then we are scanning for installation profiles themselves.
      // In this case, profiles are sorted by origin only.
      elseif ($this->profileDirectories === NULL || $this->profileDirectories === []) {
        $origins[$key] = static::ORIGIN_PROFILE;
        $profiles[$key] = NULL;
      }
      else {
        // Apply the weight of the originating profile directory.
        foreach ($this->profileDirectories as $weight => $profile_path) {
          if (strpos($file->getPath(), $profile_path) === 0) {
            $origins[$key] = static::ORIGIN_PROFILE;
            $profiles[$key] = $weight;
            continue 2;
          }
        }
      }
    }
    // Now sort the extensions by origin and installation profile(s).
    // The result of this multisort can be depicted like the following matrix,
    // whereas the first integer is the weight of the originating directory and
    // the second is the weight of the originating installation profile:
    // 0   core/modules/node/node.module
    // 1 0 profiles/parent_profile/modules/parent_module/parent_module.module
    // 1 1 core/profiles/testing/modules/compatible_test/compatible_test.module
    // 2   sites/all/modules/common/common.module
    // 3   modules/devel/devel.module
    // 4   sites/default/modules/custom/custom.module
    array_multisort($origins, SORT_ASC, $profiles, SORT_ASC, $all_files);

    return $all_files;
  }

  /**
   * Processes the filtered and sorted list of extensions.
   *
   * Extensions discovered in later search paths override earlier, unless they
   * are not compatible with the current version of Drupal core.
   *
   * @param \Drupal\Core\Extension\Extension[] $all_files
   *   The sorted list of all extensions that were found.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   The filtered list of extensions, keyed by extension name.
   */
  protected function process(array $all_files) {
    $files = array();
    // Duplicate files found in later search directories take precedence over
    // earlier ones; they replace the extension in the existing $files array.
    foreach ($all_files as $file) {
      $files[$file->getName()] = $file;
    }
    return $files;
  }

}
