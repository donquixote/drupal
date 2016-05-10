<?php

namespace Drupal\Core\Extension\DirectoryToFiles;

use Drupal\Component\Utility\UtilBase;

final class DirectoryToFilesSingleton extends UtilBase {

  /**
   * @var array
   */
  private static $instances = [];

  /**
   * @param string $root
   * @param string $include_tests
   *
   * @return \Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface
   */
  public static function getInstance($root, $include_tests) {
    return isset(self::$instances[$root][(int)$include_tests])
      ? self::$instances[$root][(int)$include_tests]
      : self::$instances[$root][(int)$include_tests] = self::createInstance($root, $include_tests);
  }

  /**
   * @param string $root
   * @param bool $include_tests
   *
   * @return \Drupal\Core\Extension\DirectoryToFiles\DirectoryToFiles_Buffer
   */
  public static function createInstance($root, $include_tests) {

    $instance = DirectoryToFiles_Readdir::create($root, $include_tests);
    return new DirectoryToFiles_Buffer($instance);
  }

}
