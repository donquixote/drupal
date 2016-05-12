<?php

namespace Drupal\Core\Extension\RawExtensionsByName;

use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface;

class RawExtensionsByName_FromRawExtensionsByType implements RawExtensionsByNameInterface {

  /**
   * @var \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface
   */
  private $rawExtensionsByType;

  /**
   * @var string
   */
  private $type;

  /**
   * @param \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface $rawExtensionsByType
   * @param string $type
   */
  public function __construct(RawExtensionsByTypeInterface $rawExtensionsByType, $type) {
    $this->rawExtensionsByType = $rawExtensionsByType;
    $this->type = $type;
  }

  /**
   * Resets any stored or cached extension list.
   *
   * @return $this
   */
  public function reset() {
    // TODO: Implement reset() method.
  }

  /**
   * Returns all available extensions, with $extension->info possibly NOT yet
   * filled in.
   *
   * It can happen that other components further modify these objects, and add
   * the ->info array and more.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   Format: $[$extension_name] = $extension
   *   E.g. $['system'] = new Extension(..)
   */
  public function getRawExtensions() {
    $extensions_by_type = $this->rawExtensionsByType->getRawExtensionsByType();
    return array_key_exists($this->type, $extensions_by_type)
      ? $extensions_by_type[$this->type]
      : [];
  }
}
