<?php

namespace Drupal\Core\Extension\SearchdirToFiles;

/**
 * Implementation that can be useful in unit tests.
 */
class SearchdirToFiles_StaticGrep implements SearchdirToFilesInterface {

  /**
   * @var string[]
   */
  private $files;

  /**
   * @param string[] $files
   *   Format: $[] = 'core/modules/system/system.info.yml';
   */
  function __construct(array $files) {
    $this->files = $files;
  }

  /**
   * Gets the paths to *.info.yml files found in the specified directory tree.
   *
   * @param string $searchdir
   *   E.g. 'core/modules'
   *
   * @return string[]
   *   Format: $[] = 'core/modules/system/system.info.yml'
   */
  public function searchdirGetFiles($searchdir) {
    return preg_grep('@^' . preg_quote($searchdir, '@') . '/@', $this->files);
  }
}
