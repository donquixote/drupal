<?php

namespace Drupal\Core\Extension\FilesToInfo;

class FilesToInfo_Static implements FilesToInfoInterface {

  /**
   * @var array[]
   */
  private $infoByFile;

  /**
   * @param array[] $info_by_file
   */
  public function __construct(array $info_by_file) {
    $this->infoByFile = $info_by_file;
  }

  /**
   * @param string[] $files
   *   Format: $[] = 'core/modules/system/system.info.yml'
   *
   * @return array[]
   *   Format: $['core/modules/system/system.info.yml'] = $info
   */
  public function filesGetInfoArrays(array $files) {
    return array_intersect_key($this->infoByFile, array_fill_keys($files, TRUE));
  }
}
