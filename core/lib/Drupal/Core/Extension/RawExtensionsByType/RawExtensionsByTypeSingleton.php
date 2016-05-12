<?php

namespace Drupal\Core\Extension\RawExtensionsByType;

use Drupal\Component\Utility\UtilBase;
use Drupal\Core\Extension\ProfileName\ProfileName_DrupalGetProfile;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common;
use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedSingleton;

final class RawExtensionsByTypeSingleton extends UtilBase {

  /**
   * @var \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface[][]
   */
  private static $instances = [];

  /**
   * @param string $root
   * @param bool $include_tests
   *
   * @return \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface
   */
  public static function getInstance($root, $include_tests = FALSE) {
    return isset(self::$instances[$root][(int)$include_tests])
      ? self::$instances[$root][(int)$include_tests]
      : self::$instances[$root][(int)$include_tests] = self::createSingletonInstance($root, $include_tests);
  }

  /**
   * @param string $root
   * @param bool $include_tests
   *
   * @return \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface
   */
  private static function createSingletonInstance($root, $include_tests = FALSE) {
    return RawExtensionsByType_FromRawExtensionsGrouped::create(
      new SearchdirPrefixes_Common(),
      SearchdirToRawExtensionsGroupedSingleton::getInstance($root, $include_tests),
      new ProfileName_DrupalGetProfile());
  }

}
