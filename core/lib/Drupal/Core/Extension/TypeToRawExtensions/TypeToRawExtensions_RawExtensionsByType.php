<?php

namespace Drupal\Core\Extension\TypeToRawExtensions;

use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface;

/**
 * Adapter from RawExtensionsByType* to TypeToRawExtensions*.
 */
class TypeToRawExtensions_RawExtensionsByType implements TypeToRawExtensionsInterface {

  /**
   * @var \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface
   */
  private $rawExtensionsByType;

  /**
   * @param \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface $rawExtensionsByType
   *   The wrapped object, possibly already coming with a buffer decorator.
   */
  public function __construct(RawExtensionsByTypeInterface $rawExtensionsByType) {
    $this->rawExtensionsByType = $rawExtensionsByType;
  }

  /**
   * {@inheritdoc}
   */
  public function getRawExtensionsByName($type) {
    $extensions_by_type = $this->rawExtensionsByType->getRawExtensionsByType();
    return array_key_exists($type, $extensions_by_type)
      ? $extensions_by_type[$type]
      : [];
  }
}
