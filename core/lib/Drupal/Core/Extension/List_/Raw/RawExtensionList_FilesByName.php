<?php

namespace Drupal\Core\Extension\List_\Raw;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\FilesByName\FilesByNameInterface;

class RawExtensionList_FilesByName implements RawExtensionListInterface {

  /**
   * @var \Drupal\Core\Extension\FilesByName\FilesByNameInterface
   */
  private $filesByName;

  /**
   * @var string
   */
  private $root;

  /**
   * @var string
   */
  private $type;

  /**
   * @var string
   */
  private $filenameSuffix;

  /**
   * @param \Drupal\Core\Extension\FilesByName\FilesByNameInterface $filesByName
   * @param string $root
   * @param string $type
   *
   * @return \Drupal\Core\Extension\List_\Raw\RawExtensionListInterface
   */
  static function create(FilesByNameInterface $filesByName, $root, $type) {
    $filename_suffix = $type === 'theme_engine'
      ? '.engine'
      : '.' . $type;
    return new self($filesByName, $root, $type, $filename_suffix);
  }

  /**
   * @param \Drupal\Core\Extension\FilesByName\FilesByNameInterface $filesByName
   * @param string $root
   *   Drupal root directory.
   * @param string $type
   *   E.g. 'module' or 'theme_engine'
   * @param string $filenameSuffix
   *   E.g. '.module' or '.engine'
   */
  function __construct(FilesByNameInterface $filesByName, $root, $type, $filenameSuffix) {
    $this->filesByName = $filesByName;
    $this->root = $root;
    $this->type = $type;
    $this->filenameSuffix = $filenameSuffix;
  }

  /**
   * Resets any stored or cached extension list.
   *
   * @return $this
   */
  public function reset() {
    // @todo Reset $this->filesByName.
  }

  /**
   * Returns all available extensions, with $extension->info possibly NOT yet
   * filled in.
   *
   * It can happen that other components further modify these objects, and add
   * the ->info array and more.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  public function getRawExtensions() {

    $files_by_name = $this->filesByName->getFilesByName();

    $extensions = [];
    foreach ($files_by_name as $extension_name => $yml_file) {

      // E.g. 'system.module'.
      $filename = $extension_name . $this->filenameSuffix;

      $extension = new Extension($this->root, $this->type, $yml_file, $filename);

      $extensions[$extension_name] = $extension;
    }

    return $extensions;
  }
}
