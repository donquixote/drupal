<?php

namespace Drupal\Core\Extension\FilesByType;

class FilesByType_Buffer implements FilesByTypeInterface {

  /**
   * @var \Drupal\Core\Extension\FilesByType\FilesByTypeInterface
   */
  private $decorated;

  /**
   * @var string[][]|null
   */
  private $buffer;

  /**
   * @param \Drupal\Core\Extension\FilesByType\FilesByTypeInterface $decorated
   */
  public function __construct(FilesByTypeInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * Gets all info files for all extension types.
   *
   * @return string[][]
   *   Format: $[$extension_type][$extension_name] = $extension_info_file
   *   E.g. $['module']['system'] = 'core/modules/system/system.info.yml'
   */
  public function getFilesByType() {
    return $this->buffer !== NULL
      ? $this->buffer
      : $this->buffer = $this->decorated->getFilesByType();
  }
}
