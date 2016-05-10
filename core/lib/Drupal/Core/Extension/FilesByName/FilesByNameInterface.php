<?php

namespace Drupal\Core\Extension\FilesByName;

interface FilesByNameInterface {

  /**
   * Provides an array of extension file paths by machine name.
   *
   * @return string[]
   *   Format: $['system'] = 'core/modules/system/system.info.yml'
   */
  public function getFilesByName();

}
