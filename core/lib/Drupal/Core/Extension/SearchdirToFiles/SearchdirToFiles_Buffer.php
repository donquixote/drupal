<?php

namespace Drupal\Core\Extension\SearchdirToFiles;

class SearchdirToFiles_Buffer implements SearchdirToFilesInterface {

  /**
   * @var \Drupal\Core\Extension\SearchdirToFiles\SearchdirToFilesInterface
   */
  private $decorated;

  /**
   * @var string[]
   *   Format: $['core/modules'][] = 'core/modules/system/system.info.yml'
   */
  private $buffer = [];

  /**
   * @param \Drupal\Core\Extension\SearchdirToFiles\SearchdirToFilesInterface $decorated
   */
  public function __construct(SearchdirToFilesInterface $decorated) {
    $this->decorated = $decorated;
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
    return array_key_exists($searchdir, $this->buffer)
      ? $this->buffer[$searchdir]
      : $this->buffer[$searchdir] = $this->decorated->searchdirGetFiles($searchdir);
  }
}
