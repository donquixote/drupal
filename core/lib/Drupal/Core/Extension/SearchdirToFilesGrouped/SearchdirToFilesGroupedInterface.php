<?php

namespace Drupal\Core\Extension\SearchdirToFilesGrouped;

interface SearchdirToFilesGroupedInterface {

  /**
   * @param string $searchdir_prefix
   *   E.g. 'core/' or 'sites/default/'
   *
   * @return string[][][]
   *   Format: $[$extension_type][$subdir_name][$name] = $file
   *   E.g. $['module']['modules']['system'] = 'core/modules/system/system.module'
   */
  public function getFilesGrouped($searchdir_prefix);

}
