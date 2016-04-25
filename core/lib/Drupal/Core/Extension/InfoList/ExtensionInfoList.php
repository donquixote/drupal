<?php

namespace Drupal\Core\Extension\InfoList;

use Drupal\Core\Extension\List_\ExtensionListInterface;

class ExtensionInfoList implements ExtensionInfoListInterface {

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
  public function getAllInfo() {
    $info = [];
    foreach ($this->extensionList->listExtensions() as $name => $extension) {
      $info[$name] = $extension->info;
    }
    return $info;
  }
}
