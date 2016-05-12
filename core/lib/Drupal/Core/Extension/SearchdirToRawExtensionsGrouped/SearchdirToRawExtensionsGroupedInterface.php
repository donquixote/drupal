<?php

namespace Drupal\Core\Extension\SearchdirToRawExtensionsGrouped;

interface SearchdirToRawExtensionsGroupedInterface {

  /**
   * @param string $searchdir_prefix
   *   E.g. 'core/' or '' or 'sites/default/'.
   *
   * @return \Drupal\Core\Extension\Extension[][][]
   *   Format: $[$extension_type][$subdir_name][$name] = $extension
   *   E.g. $['module']['modules']['system'] = new Extension(..)
   */
  public function getRawExtensionsGrouped($searchdir_prefix);

}
