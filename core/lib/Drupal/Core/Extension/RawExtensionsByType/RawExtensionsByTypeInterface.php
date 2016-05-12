<?php

namespace Drupal\Core\Extension\RawExtensionsByType;

interface RawExtensionsByTypeInterface {

  /**
   * @return \Drupal\Core\Extension\Extension[][]
   *   Format: $[$extension_type][$extension_name] = $extension
   */
  public function getRawExtensionsByType();

}
