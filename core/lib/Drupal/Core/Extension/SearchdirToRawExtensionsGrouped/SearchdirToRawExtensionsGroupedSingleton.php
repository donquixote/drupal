<?php

namespace Drupal\Core\Extension\SearchdirToRawExtensionsGrouped;

use Drupal\Component\Utility\UtilBase;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedSingleton;

/**
 * Holds an array of singleton instances.
 *
 * This is meant for consumers with no access to the container, but which still
 * want to benefit from the caching / buffering.
 *
 * Note that this is *not* the classical singleton pattern. Other instances of
 * the classes can still be freely created, e.g. for testing. Those other
 * instances will simply not share the same buffering.
 * 
 * Note that this way of static caching is flawed, because other components do
 * not treat the cached objects as immutable. Modifications on the objects will
 * leak between usage contexts.
 * 
 * The reason this exists is to replicate the same behavior of the older
 * "ExtensionDiscovery" component.
 */
final class SearchdirToRawExtensionsGroupedSingleton extends UtilBase {

  /**
   * @var \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface[][]
   *   Format: $[$root][(int)$include_tests] = $instance
   */
  private static $instances = [];

  /**
   * Gets the singleton instance for the specified root directory and extension
   * type.
   *
   * @param string $root
   *   The Drupal root directory.
   * @param bool $include_tests
   *   TRUE, if tests directories should be included in the scanning.
   *
   * @return \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface
   */
  public static function getInstance($root, $include_tests = FALSE) {
    return isset(self::$instances[$root][(int)$include_tests])
      ? self::$instances[$root][(int)$include_tests]
      : self::$instances[$root][(int)$include_tests] = self::createInstance(
        $root,
        SearchdirToFilesGroupedSingleton::getInstance($root, $include_tests));
  }

  /**
   * Gets an instance for the specified root directory and extension type, to be
   * used in the singleton above or elsewhere.
   *
   * @param string $root
   *   The Drupal root directory.
   * @param \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface $searchdirToFilesGrouped
   *
   * @return \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface
   */
  public static function createInstance($root, SearchdirToFilesGroupedInterface $searchdirToFilesGrouped) {

    $instance = new SearchdirToRawExtensionsGrouped_Common($searchdirToFilesGrouped, $root);
    $instance = new SearchdirToRawExtensionsGrouped_Buffer($instance);

    return $instance;
  }

}
