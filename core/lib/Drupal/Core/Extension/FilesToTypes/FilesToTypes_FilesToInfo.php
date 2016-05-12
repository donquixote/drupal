<?php

namespace Drupal\Core\Extension\FilesToTypes;

use Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface;

class FilesToTypes_FilesToInfo implements FilesToTypesInterface {

  /**
   * @var \Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface
   */
  private $filesToInfo;

  /**
   * @param \Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface $filesToInfo
   */
  public function __construct(FilesToInfoInterface $filesToInfo) {
    $this->filesToInfo = $filesToInfo;
  }

  /**
   * Gets the type of each extension.
   *
   * @param string[] $files
   *   Format: $[] = 'core/modules/system/system.info.yml'
   *
   * @return string[]
   *   Format: $['core/modules/system/system.info.yml'] = 'module'
   */
  public function filesGetTypes(array $files) {
    $type_by_file = [];
    foreach ($this->filesToInfo->filesGetInfoArrays($files) as $file => $info) {
      $type = isset($info['type'])
        ? $info['type']
        : NULL;
      if (!is_string($type)) {
        // @todo What to do with invalid types?
        continue;
      }
      $type_by_file[$file] = $type;
    }
    return $type_by_file;
  }
}
