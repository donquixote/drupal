<?php

namespace Drupal\Core\Extension\TypeToRawExtensions;

use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface;
use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeSingleton;

class TypeToRawExtensionsSingleton {

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
      : self::$instances[$root][(int)$include_tests] = self::createInstance(
        RawExtensionsByTypeSingleton::getInstance($root, $include_tests));
  }

  /**
   * @param \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface $rawExtensionsByType
   *
   * @return \Drupal\Core\Extension\TypeToRawExtensions\TypeToRawExtensionsInterface
   */
  public static function createInstance(RawExtensionsByTypeInterface $rawExtensionsByType) {
    return new TypeToRawExtensions_RawExtensionsByType($rawExtensionsByType);
  }

}
