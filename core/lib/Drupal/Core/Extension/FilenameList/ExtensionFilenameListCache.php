<?php

namespace Drupal\Core\Extension\FilenameList;

use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Cache decorator for extension file list.
 */
class ExtensionFilenameListCache implements ExtensionFilenameListInterface {

  /**
   * @var \Drupal\Core\Extension\FilenameList\ExtensionFilenameListInterface
   */
  private $decorated;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * @var string
   */
  private $cacheId;

  /**
   * @param \Drupal\Core\Extension\FilenameList\ExtensionFilenameListInterface $decorated
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   * @param string $cache_id
   */
  function __construct(ExtensionFilenameListInterface $decorated, CacheBackendInterface $cache, $cache_id) {
    $this->decorated = $decorated;
    $this->cache = $cache;
    $this->cacheId = $cache_id;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->cache->delete($this->cacheId);
    $this->decorated->reset();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilenames() {
    if ($cache = $this->cache->get($this->cacheId)) {
      return $cache->data;
    }
    // Resolve cache miss.
    $file_names = $this->decorated->getFilenames();
    $this->cache->set($this->cacheId, $file_names);
    return $file_names;
  }
}
