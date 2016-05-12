<?php

namespace Drupal\Core\Extension\TypeToRawExtensions;

interface TypeToRawExtensionsInterface {

  /**
   * @param string $type
   *   E.g. 'module' or 'theme_engine'.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  public function getRawExtensionsByName($type);

}
