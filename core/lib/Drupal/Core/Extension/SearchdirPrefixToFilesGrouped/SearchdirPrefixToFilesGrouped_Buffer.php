<?php

namespace Drupal\Core\Extension\SearchdirPrefixToFilesGrouped;

class SearchdirPrefixToFilesGrouped_Buffer implements SearchdirPrefixToFilesGroupedInterface {

  /**
   * @var \Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface
   */
  private $decorated;

  /**
   * @var string[][][]
   *   Format: $[$extension_type][$subdir_name][$name] = $file
   *   E.g. $['module']['modules']['system'] = 'core/modules/system/system.module'
   */
  private $buffer = [];

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface $decorated
   */
  function __construct(SearchdirPrefixToFilesGroupedInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * @param string $searchdir_prefix
   *   E.g. 'core/' or 'sites/default/'
   *
   * @return string[][][]
   *   Format: $[$extension_type][$subdir_name][$name] = $file
   *   E.g. $['module']['modules']['system'] = 'core/modules/system/system.module'
   */
  public function searchdirPrefixGetFilesGrouped($searchdir_prefix) {
    return array_key_exists($searchdir_prefix, $this->buffer)
      ? $this->buffer[$searchdir_prefix]
      : $this->buffer[$searchdir_prefix] = $this->decorated->searchdirPrefixGetFilesGrouped($searchdir_prefix);
  }
}
