<?php

namespace Drupal\Core\Extension\ProfileDirs;

use Drupal\Component\Utility\UtilBase;
use Drupal\Core\Extension\FilesByName\FilesByName_Profile;
use Drupal\Core\Extension\ProfileName\ProfileName_DrupalGetProfile;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedSingleton;

final class ProfileDirsSingleton extends UtilBase {

  /**
   * @var \Drupal\Core\Extension\ProfileDirs\ProfileDirsInterface[][]
   *   Format: $[$root][(int)$include_tests] = $instance
   */
  private static $instances = [];

  /**
   * @param string $root
   * @param string $include_tests
   *
   * @return \Drupal\Core\Extension\ProfileDirs\ProfileDirsInterface
   */
  public static function getInstance($root, $include_tests) {
    return isset(self::$instances[$root][(int)$include_tests])
      ? self::$instances[$root][(int)$include_tests]
      : self::$instances[$root][(int)$include_tests] = self::createInstance(
        SearchdirToFilesGroupedSingleton::getInstance($root, $include_tests));
  }

  /**
   * @param \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface $searchdirToFilesGrouped
   *
   * @return \Drupal\Core\Extension\ProfileDirs\ProfileDirsInterface
   */
  public static function createInstance(SearchdirToFilesGroupedInterface $searchdirToFilesGrouped) {
    $searchdirPrefixes = new SearchdirPrefixes_Common();
    $profileFilesByName = new FilesByName_Profile($searchdirPrefixes, $searchdirToFilesGrouped);
    $profileNameProvider = new ProfileName_DrupalGetProfile();
    return new ProfileDirs_Active($profileFilesByName, $profileNameProvider);
  }

}
