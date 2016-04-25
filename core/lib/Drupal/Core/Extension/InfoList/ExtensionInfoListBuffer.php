<?php

namespace Drupal\Core\Extension\InfoList;

class ExtensionInfoListBuffer implements ExtensionInfoListInterface {

  /**
   * @var \Drupal\Core\Extension\InfoList\ExtensionInfoListInterface
   */
  private $decorated;

  /**
   * @var array[]|null
   */
  private $extensionInfo;

  /**
   * @param \Drupal\Core\Extension\InfoList\ExtensionInfoListInterface $decorated
   */
  function __construct(ExtensionInfoListInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->extensionInfo = NULL;
    $this->decorated->reset();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllInfo() {
    return $this->extensionInfo !== NULL
      ? $this->extensionInfo
      : $this->extensionInfo = $this->decorated->getAllInfo();
  }
}
