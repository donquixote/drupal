<?php

namespace Drupal\Core\Extension\FilesToTypes;

interface FilesToTypesInterface {

  /**
   * Gets the type of each extension.
   * 
   * @param string[] $files
   *   Format: $[] = 'core/modules/system/system.info.yml'
   *
   * @return string[]
   *   Format: $['core/modules/system/system.info.yml'] = 'module'
   */
  public function filesGetTypes(array $files);

}
