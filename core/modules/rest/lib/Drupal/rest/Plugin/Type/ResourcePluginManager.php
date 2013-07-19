<?php

/**
 * @file
 * Definition of Drupal\rest\Plugin\Type\ResourcePluginManager.
 */

namespace Drupal\rest\Plugin\Type;

use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\Factory\ReflectionFactory;
use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\CacheDecorator;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Manages discovery and instantiation of resource plugins.
 */
class ResourcePluginManager extends PluginManagerBase {

  /**
   * Overrides Drupal\Component\Plugin\PluginManagerBase::__construct().
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces) {
    // Create resource plugin derivatives from declaratively defined resources.
    $this->discovery = new AnnotatedClassDiscovery($root_namespaces, 'rest\resource');
    $this->discovery = new DerivativeDiscoveryDecorator($this->discovery);
    $this->discovery = new AlterDecorator($this->discovery, 'rest_resource');
    $this->discovery = new CacheDecorator($this->discovery, 'rest');

    $this->factory = new ReflectionFactory($this->discovery);
  }

  /**
   * Overrides Drupal\Component\Plugin\PluginManagerBase::getInstance().
   */
  public function getInstance(array $options){
    if (isset($options['id'])) {
      return $this->createInstance($options['id']);
    }
  }
}
