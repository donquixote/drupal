<?php

namespace Drupal\Core\Extension\ExtensionsByName;

use Drupal\Core\Cache\CacheBackendInterface;

class ExtensionsByName_Cache implements ExtensionsByNameInterface {

  /**
   * @var \Drupal\Core\Extension\ExtensionsByName\ExtensionsByNameInterface
   */
  private $decorated;

  /**
   * The cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * @var string
   */
  private $cache_id;

  /**
   * Constructs a new ExtensionList instance.
   *
   * @param \Drupal\Core\Extension\ExtensionsByName\ExtensionsByNameInterface $decorated
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   * @param string $cache_id
   */
  public function __construct(ExtensionsByNameInterface $decorated, CacheBackendInterface $cache, $cache_id) {
    $this->cache = $cache;
    $this->decorated = $decorated;
    $this->cache_id = $cache_id;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->cache->delete($this->cache_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensions() {
    if ($cache = $this->cache->get($this->cache_id)) {
      return $cache->data;
    }
    $extensions = $this->decorated->getExtensions();
    $this->cache->set($this->cache_id, $extensions);
    return $extensions;
  }
}
