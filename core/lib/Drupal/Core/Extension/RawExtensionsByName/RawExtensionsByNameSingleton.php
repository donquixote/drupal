<?php

namespace Drupal\Core\Extension\RawExtensionsByName;

use Drupal\Component\Utility\UtilBase;
use Drupal\Core\Extension\ProfileDirs\ProfileDirsSingleton;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common;
use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedSingleton;

final class RawExtensionsByNameSingleton extends UtilBase {

  /**
   * @var \Drupal\Core\Extension\RawExtensionsByName\RawExtensionsByNameInterface[][][]
   *   Format: $[$root][$type][(int)$include_tests] = $instance
   */
  private static $instances = [];

  /**
   * @param string $root
   * @param string $type
   * @param bool $include_tests
   *
   * @return \Drupal\Core\Extension\RawExtensionsByName\RawExtensionsByNameInterface
   */
  public static function getInstance($root, $type, $include_tests = FALSE) {
    return isset(self::$instances[$root][$type][(int)$include_tests])
      ? self::$instances[$root][$type][(int)$include_tests]
      : self::$instances[$root][$type][(int)$include_tests] = self::createInstanceFromSingletons($root, $type, $include_tests);
  }

  /**
   * @param string $root
   * @param string $type
   * @param bool $include_tests
   *
   * @return \Drupal\Core\Extension\RawExtensionsByName\RawExtensionsByNameInterface
   */
  private static function createInstanceFromSingletons($root, $type, $include_tests) {
    $searchdirPrefixes = new SearchdirPrefixes_Common();
    $searchdirToRawExtensionsGrouped = SearchdirToRawExtensionsGroupedSingleton::getInstance($root, $include_tests);
    if ($type === 'profile') {
      return new RawExtensionsByName_Profile($searchdirPrefixes, $searchdirToRawExtensionsGrouped, 'profile');
    }
    else {
      $activeProfileDirs = ProfileDirsSingleton::getInstance($root, $include_tests);
      return new RawExtensionsByName_NonProfile($searchdirPrefixes, $searchdirToRawExtensionsGrouped, $type, $activeProfileDirs);
    }
  }

}
