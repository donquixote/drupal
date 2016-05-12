<?php

namespace Drupal\Core\Extension\RawExtensionsByType;

class RawExtensionsByType_Buffer implements RawExtensionsByTypeInterface {

  /**
   * @var \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface
   */
  private $decorated;

  /**
   * @var \Drupal\Core\Extension\Extension[][]|null
   */
  private $buffer;

  /**
   * @param \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface $decorated
   */
  public function __construct(RawExtensionsByTypeInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * @return \Drupal\Core\Extension\Extension[][]
   *   Format: $[$extension_type][$extension_name] = $extension
   */
  public function getRawExtensionsByType() {
    return $this->buffer !== NULL
      ? $this->buffer
      : $this->buffer = $this->decorated->getRawExtensionsByType();
  }
}
