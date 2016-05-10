<?php

namespace Drupal\Core\Extension\FilesToInfo;

interface FilesToInfoInterface {

  /**
   * @param string[] $files
   *   Format: $[] = 'core/modules/system/system.info.yml'
   *
   * @return array[]
   *   Format: $['core/modules/system/system.info.yml'] = $info
   */
  public function filesGetInfoArrays(array $files);
  
}
