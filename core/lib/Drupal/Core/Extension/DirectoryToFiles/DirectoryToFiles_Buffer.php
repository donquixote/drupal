<?php

namespace Drupal\Core\Extension\DirectoryToFiles;

class DirectoryToFiles_Buffer implements DirectoryToFilesInterface {

  /**
   * @var \Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface
   */
  private $decorated;

  /**
   * @var string[]
   *   Format: $['core/modules'][] = 'core/modules/system/system.info.yml'
   */
  private $buffer = [];

  /**
   * @param \Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface $decorated
   */
  public function __construct(DirectoryToFilesInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * Resets all cached data.
   */
  public function reset() {
    $this->decorated->reset();
    $this->buffer = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFiles($directory) {
    return array_key_exists($directory, $this->buffer)
      ? $this->buffer[$directory]
      : $this->buffer[$directory] = $this->decorated->getFiles($directory);
  }
}
