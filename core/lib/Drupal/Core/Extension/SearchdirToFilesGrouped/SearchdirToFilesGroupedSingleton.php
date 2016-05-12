<?php

namespace Drupal\Core\Extension\SearchdirToFilesGrouped;

use Drupal\Component\Utility\UtilBase;
use Drupal\Core\Extension\FilesToTypes\FilesToTypes_Regex;
use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface;
use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesSingleton;

final class SearchdirToFilesGroupedSingleton extends UtilBase {

  /**
   * @var \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface[][]
   *   Format: $[$root][(int)$include_tests] = $searchdirToFilesGrouped
   */
  private static $instances = [];

  /**
   * @param string $root
   * @param bool $include_tests
   *
   * @return \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface
   */
  public static function getInstance($root, $include_tests) {
    return isset(self::$instances[$root][(int)$include_tests])
      ? self::$instances[$root][(int)$include_tests]
      : self::$instances[$root][(int)$include_tests] = self::createInstance(
        $root,
        DirectoryToFilesSingleton::getInstance($root, $include_tests));
  }

  /**
   * @param string $root
   * @param \Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface $directoryToFiles
   *
   * @return \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface
   */
  public static function createInstance($root, DirectoryToFilesInterface $directoryToFiles) {

    $filesToTypes = new FilesToTypes_Regex($root);

    return SearchdirToFilesGrouped_Common::createFromComponents($directoryToFiles, $filesToTypes);
  }

}
