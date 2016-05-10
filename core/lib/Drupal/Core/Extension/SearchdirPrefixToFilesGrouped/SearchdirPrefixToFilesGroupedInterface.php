<?php

namespace Drupal\Core\Extension\SearchdirPrefixToFilesGrouped;

interface SearchdirPrefixToFilesGroupedInterface {

  /**
   * @param string $searchdir_prefix
   *   E.g. 'core/' or 'sites/default/'
   *
   * @return string[][][]
   *   Format: $[$extension_type][$subdir_name][$name] = $file
   *   E.g. $['module']['modules']['system'] = 'core/modules/system/system.module'
   */
  public function searchdirPrefixGetFilesGrouped($searchdir_prefix);

}
