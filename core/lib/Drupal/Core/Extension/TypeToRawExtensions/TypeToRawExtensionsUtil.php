<?php

namespace Drupal\Core\Extension\TypeToRawExtensions;

use Drupal\Component\Utility\UtilBase;
use Drupal\Core\Extension\ProfileName\ProfileName_DrupalGetProfile;
use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByType_FromRawExtensionsGrouped;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common;
use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedSingleton;

final class TypeToRawExtensionsUtil extends UtilBase {

  /**
   * Creates a default implementation.
   *
   * @param string $root
   *   (optional) The Drupal root directory. Defaults to \Drupal::root().
   * @param bool $include_tests
   *   TRUE, to also scan tests directories.
   *
   * @return \Drupal\Core\Extension\TypeToRawExtensions\TypeToRawExtensionsInterface
   */
  public static function create($root = NULL, $include_tests = FALSE) {
    if ($root === NULL) {
      $root = \Drupal::root();
    }
    $rawExtensionsByType = RawExtensionsByType_FromRawExtensionsGrouped::create(
      new SearchdirPrefixes_Common(),
      SearchdirToRawExtensionsGroupedSingleton::getInstance($root, $include_tests),
      new ProfileName_DrupalGetProfile());
    return new TypeToRawExtensions_RawExtensionsByType($rawExtensionsByType);
  }

}
