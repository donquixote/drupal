<?php

namespace Drupal\Core\Extension\DirectoryToFiles;

/**
 * Implementation with a fixed list, useful for unit tests.
 */
class DirectoryToFiles_StaticGrep implements DirectoryToFilesInterface {

  /**
   * @var string[]
   */
  private $files;

  /**
   * @param string[] $files
   *   Format: $[] = 'core/modules/system/system.info.yml';
   */
  public function __construct(array $files) {
    $this->files = $files;
  }

  /**
   * Resets all cached data.
   */
  public function reset() {
    // Nothing to do.
  }

  /**
   * Gets the paths to *.info.yml files found in the specified directory tree.
   *
   * @param string $directory
   *   E.g. 'core/modules'
   *
   * @return string[]
   *   Format: $[] = 'core/modules/system/system.info.yml'
   */
  public function getFiles($directory) {
    return preg_grep('@^' . preg_quote($directory, '@') . '/@', $this->files);
  }
}
