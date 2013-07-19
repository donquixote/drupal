<?php

/**
 * Contains \Drupal\block\Plugin\Type\BlockManager.
 */

namespace Drupal\block\Plugin\Type;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;
/**
 * Manages discovery and instantiation of block plugins.
 *
 * @todo Add documentation to this class.
 *
 * @see \Drupal\block\BlockPluginInterface
 */
class BlockManager extends DefaultPluginManager {

  /**
   * Constructs a new \Drupal\block\Plugin\Type\BlockManager object.
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($root_namespaces, 'Block');
    $this->alterInfo($module_handler, 'block');
    $this->setCacheBackend($cache_backend, $language_manager, 'block_plugins');
  }
}
