<?php

namespace Drupal\Core\Extension\RawExtensionsByType;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\FilesByType\FilesByTypeInterface;

class RawExtensionsByType_FromFilesByType implements RawExtensionsByTypeInterface {

  /**
   * @var string
   */
  private $root;

  /**
   * @var \Drupal\Core\Extension\FilesByType\FilesByTypeInterface
   */
  private $filesByType;

  /**
   * @param string $root
   * @param \Drupal\Core\Extension\FilesByType\FilesByTypeInterface $filesByType
   */
  public function __construct($root, FilesByTypeInterface $filesByType) {
    $this->root = $root;
    $this->filesByType = $filesByType;
  }

  /**
   * @return \Drupal\Core\Extension\Extension[][]
   *   Format: $[$extension_type][$extension_name] = $extension
   */
  public function getRawExtensionsByType() {

    $extensions_by_type_and_name = [];
    foreach ($this->filesByType->getFilesByType() as $type => $files_by_name) {

      $filename_suffix = $type === 'theme_engine'
        ? '.engine'
        : '.' . $type;

      foreach ($files_by_name as $name => $yml_file) {

        // E.g. 'system.module'.
        $filename = $name . $filename_suffix;

        $extensions_by_type_and_name[$type][$name] = new Extension($this->root, $type, $yml_file, $filename);
      }
    }

    return $extensions_by_type_and_name;
  }
}
