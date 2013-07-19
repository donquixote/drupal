<?php

/**
 * @file
 * Contains \Drupal\filter\FilterPluginManager.
 */

namespace Drupal\filter;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\CacheDecorator;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Manages text processing filters.
 *
 * @see hook_filter_info_alter()
 */
class FilterPluginManager extends PluginManagerBase {

  /**
   * Constructs a FilterPluginManager object.
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces) {
    $this->discovery = new AnnotatedClassDiscovery($root_namespaces, 'Filter', 'Drupal\filter\Annotation\Filter');
    $this->discovery->addAnnotationNamespace('Drupal\filter\Annotation');
    $this->discovery = new AlterDecorator($this->discovery, 'filter_info');
    $cache_key = 'filter_plugins:' . language(Language::TYPE_INTERFACE)->id;
    $cache_tags = array('filter_formats' => TRUE);
    $this->discovery = new CacheDecorator($this->discovery, $cache_key, 'cache', CacheBackendInterface::CACHE_PERMANENT, $cache_tags);
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = array(), FilterBag $filter_bag = NULL) {
    $plugin_definition = $this->discovery->getDefinition($plugin_id);
    $plugin_class = DefaultFactory::getPluginClass($plugin_id, $plugin_definition);
    return new $plugin_class($configuration, $plugin_id, $plugin_definition, $filter_bag);
  }

}
