<?php

namespace Drupal\Core\Extension\SearchdirToFiles;

interface SearchdirToFilesInterface {

  /**
   * Gets the paths to *.info.yml files found in the specified directory tree.
   * 
   * @param string $searchdir
   *   E.g. 'core/modules'
   *
   * @return string[]
   *   Format: $[] = 'core/modules/system/system.info.yml'
   */
  public function searchdirGetFiles($searchdir);

}
