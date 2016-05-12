<?php

namespace Drupal\Core\Extension\SearchdirToFilesGrouped;

class SearchdirToFilesGrouped_Buffer implements SearchdirToFilesGroupedInterface {

  /**
   * @var \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface
   */
  private $decorated;

  /**
   * @var string[][][]
   *   Format: $[$extension_type][$subdir_name][$name] = $file
   *   E.g. $['module']['modules']['system'] = 'core/modules/system/system.module'
   */
  private $buffer = [];

  /**
   * @param \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface $decorated
   */
  public function __construct(SearchdirToFilesGroupedInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * Resets all cached or buffered data.
   */
  public function reset() {
    $this->decorated->reset();
    $this->buffer = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFilesGrouped($searchdir_prefix) {
    return array_key_exists($searchdir_prefix, $this->buffer)
      ? $this->buffer[$searchdir_prefix]
      : $this->buffer[$searchdir_prefix] = $this->decorated->getFilesGrouped($searchdir_prefix);
  }
}
