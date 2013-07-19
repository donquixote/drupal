<?php

/**
 * @file
 * Contains \Drupal\tour\TipPluginManager.
 */

namespace Drupal\tour;

use Drupal\Component\Plugin\Discovery\ProcessDecorator;
use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\CacheDecorator;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Configurable tour manager.
 */
class TipPluginManager extends PluginManagerBase {

  /**
   * Overrides \Drupal\Component\Plugin\PluginManagerBase::__construct().
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces) {
    $this->discovery = new AnnotatedClassDiscovery($root_namespaces, 'tour\tip', 'Drupal\tour\Annotation\Tip');
    $this->discovery->addAnnotationNamespace('Drupal\tour\Annotation');
    $this->discovery = new AlterDecorator($this->discovery, 'tour_tips_info');
    $this->discovery = new CacheDecorator($this->discovery, 'tour');

    $this->factory = new ContainerFactory($this->discovery);
  }

}
