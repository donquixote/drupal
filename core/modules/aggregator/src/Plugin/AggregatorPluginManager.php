<?php

/**
 * @file
 * Contains \Drupal\aggregator\Plugin\AggregatorPluginManager.
 */

namespace Drupal\aggregator\Plugin;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Language\Language;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\CacheDecorator;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Manages aggregator plugins.
 */
class AggregatorPluginManager extends PluginManagerBase {

  /**
   * Constructs a AggregatorPluginManager object.
   *
   * @param string $type
   *   The plugin type, for example fetcher.
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   */
  public function __construct($type, SearchableNamespacesInterface $root_namespaces) {
    $type_annotations = array(
      'fetcher' => 'Drupal\aggregator\Annotation\AggregatorFetcher',
      'parser' => 'Drupal\aggregator\Annotation\AggregatorParser',
      'processor' => 'Drupal\aggregator\Annotation\AggregatorProcessor',
    );

    $this->discovery = new AnnotatedClassDiscovery($root_namespaces, "aggregator\\$type", $type_annotations[$type]);
    $this->discovery->addAnnotationNamespace('Drupal\aggregator\Annotation');
    $this->discovery = new CacheDecorator($this->discovery, "aggregator_$type:" . language(Language::TYPE_INTERFACE)->id);
    $this->factory = new DefaultFactory($this->discovery);
  }
}
