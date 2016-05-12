<?php

namespace Drupal\Core\Extension\FilesByType;

interface FilesByTypeInterface {

  /**
   * Gets all info files for all extension types.
   *
   * @return string[][]
   *   Format: $[$extension_type][$extension_name] = $extension_info_file
   *   E.g. $['module']['system'] = 'core/modules/system/system.info.yml'
   */
  public function getFilesByType();

}
