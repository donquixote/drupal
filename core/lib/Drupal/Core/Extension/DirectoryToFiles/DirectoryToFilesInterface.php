<?php

namespace Drupal\Core\Extension\DirectoryToFiles;

interface DirectoryToFilesInterface {

  /**
   * Gets the paths to *.info.yml files found in the specified directory tree.
   * 
   * @param string $directory
   *   Directory relative to Drupal root, e.g. 'core/modules'.
   *
   * @return string[]
   *   Format: $[] = 'core/modules/system/system.info.yml'
   *   Array of files found in the directory, relative to Drupal root.
   */
  public function getFiles($directory);

}
