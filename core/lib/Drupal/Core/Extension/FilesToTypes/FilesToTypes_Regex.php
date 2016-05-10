<?php

namespace Drupal\Core\Extension\FilesToTypes;

class FilesToTypes_Regex implements FilesToTypesInterface {

  /**
   * @var string
   */
  private $root;

  /**
   * @param string $root
   *   Root directory of the Drupal installation.
   */
  public function __construct($root) {
    $this->root = $root;
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
      if (NULL !== $type = $this->fileGetType($file)) {
        $type_by_file[$file] = $type;
      }
    }
    return $type_by_file;
  }

  /**
   * @param string $file
   *   E.g. 'core/modules/system/system.info.yml'
   *
   * @return string|null
   *   E.g. 'module'
   */
  private function fileGetType($file) {
    $handle = new \SplFileObject($this->root . '/' . $file);
    while (!$handle->eof()) {
      preg_match('@^type:\s*(\'|")?(\w+)\1?\s*$@', $handle->fgets(), $matches);
      if (isset($matches[2])) {
        return $matches[2];
      }
    }
    return NULL;
  }
}
