<?php

namespace Drupal\Core\Extension\FilenameList;

use Drupal\Core\Extension\List_\ExtensionListInterface;

class ExtensionFilenameList implements ExtensionFilenameListInterface {

  /**
   * @var \Drupal\Core\Extension\List_\ExtensionListInterface
   */
  private $extensionList;

  /**
   * @param \Drupal\Core\Extension\List_\ExtensionListInterface $extension_list
   */
  function __construct(ExtensionListInterface $extension_list) {
    $this->extensionList = $extension_list;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilenames() {
    $file_names = [];
    $extensions = $this->extensionList->listExtensions();
    ksort($extensions);
    foreach ($extensions as $name => $extension) {
      $file_names[$name] = $extension->getPathname();
    }
    return $file_names;
  }
}
