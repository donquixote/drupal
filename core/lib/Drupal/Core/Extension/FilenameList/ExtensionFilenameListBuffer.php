<?php

namespace Drupal\Core\Extension\FilenameList;

class ExtensionFilenameListBuffer implements ExtensionFilenameListInterface {

  /**
   * @var \Drupal\Core\Extension\FilenameList\ExtensionFilenameListInterface
   */
  private $decorated;

  /**
   * @var string[]|null
   */
  private $fileNames;

  /**
   * @param \Drupal\Core\Extension\FilenameList\ExtensionFilenameListInterface $decorated
   */
  function __construct(ExtensionFilenameListInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->fileNames = NULL;
    $this->decorated->reset();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilenames() {
    return $this->fileNames !== NULL
      ? $this->fileNames
      : $this->fileNames = $this->decorated->getFilenames();
  }
}
