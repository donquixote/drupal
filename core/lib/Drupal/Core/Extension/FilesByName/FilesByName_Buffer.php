<?php

namespace Drupal\Core\Extension\FilesByName;

class FilesByName_Buffer implements FilesByNameInterface {

  /**
   * @var \Drupal\Core\Extension\FilesByName\FilesByNameInterface
   */
  private $decorated;

  /**
   * @var string[]|null
   */
  private $buffer;

  /**
   * @param \Drupal\Core\Extension\FilesByName\FilesByNameInterface $decorated
   */
  function __construct(FilesByNameInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * Provides an array of extension file paths by machine name.
   *
   * @return string[]
   *   Format: $['system'] = 'core/modules/system/system.info.yml'
   */
  public function getFilesByName() {
    return $this->buffer !== NULL
      ? $this->buffer
      : $this->buffer = $this->decorated->getFilesByName();
  }
}
