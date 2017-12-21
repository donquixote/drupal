<?php

namespace Donquixote\Nicetrace\PathShortener;

class PathShortener_BasePaths implements PathShortenerInterface {

  /**
   * @var int[]
   *   Format: $[$basePath] = strlen($basePath)
   */
  private $basePathLengths = array();
  
  static function createFromEnvironment() {
    
  }

  /**
   * @param string[] $basePaths
   *   Format: $[] = $basePath
   */
  function __construct(array $basePaths) {
    foreach ($basePaths as $basePath) {
      $this->basePathLengths[$basePath] = strlen($basePath);
    }
  }

  /**
   * @param string $path
   *
   * @return string
   */
  function pathGetShortenedPath($path) {
    foreach ($this->basePathLengths as $basePath => $len) {
      if (strpos($path, $basePath) === 0) {
        return substr($path, $len);
      }
    }
    return $path;
  }
}
