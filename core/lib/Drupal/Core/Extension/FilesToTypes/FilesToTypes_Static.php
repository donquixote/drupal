<?php

namespace Drupal\Core\Extension\FilesToTypes;

/**
 * Implementation that can be useful in unit tests.
 */
class FilesToTypes_Static implements FilesToTypesInterface {

  /**
   * @var string[]
   */
  private $typeByFile;

  /**
   * @var null|string
   */
  private $defaultType;

  /**
   * @param string[] $typeByFile
   *   Format: $['core/modules/system/system.info.yml'] = 'module'
   * @param string|null $defaultType
   *   E.g. 'module'.
   */
  public function __construct($typeByFile, $defaultType) {
    $this->typeByFile = $typeByFile;
    $this->defaultType = $defaultType;
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
    foreach ($files as $file) {
      $type = isset($this->typeByFile[$file])
        ? $this->typeByFile[$file]
        : $this->defaultType;
      if (!is_string($type)) {
        continue;
      }
      $type_by_file[$file] = $type;
    }
    return $type_by_file;
  }
}
