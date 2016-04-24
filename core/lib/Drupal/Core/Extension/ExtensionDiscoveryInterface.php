<?php
namespace Drupal\Core\Extension;

/**
 * Discovers available extensions in the filesystem.
 *
 * To also discover test modules, add
 *
 * @code
 * $settings['extension_discovery_scan_tests'] = TRUE;
 * @encode
 * to your settings.php.
 *
 */
interface ExtensionDiscoveryInterface {

  /**
   * Discovers available extensions of a given type.
   *
   * Finds all extensions (modules, themes, etc) that exist on the site. It
   * searches in several locations. For instance, to discover all available
   * modules:
   *
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
  public function scan($type, $include_tests = NULL);
}
