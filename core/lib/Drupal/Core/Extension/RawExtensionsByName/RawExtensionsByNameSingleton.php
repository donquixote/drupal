<?php

namespace Drupal\Core\Extension\RawExtensionsByName;

use Drupal\Component\Utility\UtilBase;
use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeSingleton;

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
      : self::$instances[$root][$type][(int)$include_tests] = new RawExtensionsByName_FromRawExtensionsByType(
        RawExtensionsByTypeSingleton::getInstance($root, $include_tests),
        $type);
  }

}
