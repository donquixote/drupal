<?php

namespace Drupal\Core\Extension\InfoList;

use Drupal\Core\Cache\CacheBackendInterface;

class ExtensionInfoListCache implements ExtensionInfoListInterface {

  /**
   * @var \Drupal\Core\Extension\InfoList\ExtensionInfoListInterface
   */
  private $decorated;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * @var string
   */
  private $cache_id;

  /**
   * @param \Drupal\Core\Extension\InfoList\ExtensionInfoListInterface $decorated
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   * @param string $cache_id
   */
  function __construct(ExtensionInfoListInterface $decorated, CacheBackendInterface $cache, $cache_id) {
    $this->decorated = $decorated;
    $this->cache = $cache;
    $this->cache_id = $cache_id;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->cache->delete($this->cache_id);
    $this->decorated->reset();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllInfo() {
    if ($cache = $this->cache->get($this->cache_id)) {
      return $cache->data;
    }
    // Resolve cache miss.
    $info = $this->decorated->getAllInfo();
    $this->cache->set($this->cache_id, $info);
    return $info;
  }
}
