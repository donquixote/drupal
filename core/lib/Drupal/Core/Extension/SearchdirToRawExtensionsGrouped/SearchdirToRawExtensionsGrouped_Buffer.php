<?php

namespace Drupal\Core\Extension\SearchdirToRawExtensionsGrouped;

/**
 * Decorator that buffers the results.
 */
class SearchdirToRawExtensionsGrouped_Buffer implements SearchdirToRawExtensionsGroupedInterface {

  /**
   * @var \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface
   */
  private $decorated;

  /**
   * @var \Drupal\Core\Extension\Extension[][][][]
   *   Format: $[$searchdir_prefix][$extension_type][$subdir_name][$name] = $extension
   *   E.g. $['core/']['module']['modules']['system'] = new Extension(..)
   */
  private $buffer = [];

  /**
   * @param \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface $decorated
   */
  public function __construct(SearchdirToRawExtensionsGroupedInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * Resets all cached data.
   */
  public function reset() {
    $this->decorated->reset();
    $this->buffer = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getRawExtensionsGrouped($searchdir_prefix) {
    return isset($this->buffer[$searchdir_prefix])
      ? $this->buffer[$searchdir_prefix]
      : $this->buffer[$searchdir_prefix] = $this->decorated->getRawExtensionsGrouped($searchdir_prefix);
  }
}
