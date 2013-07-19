<?php

/**
 * @file
 * Definition of Drupal\layout\Plugin\Type\LayoutManager.
 */

namespace Drupal\layout\Plugin\Type;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\Discovery\ProcessDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Component\Plugin\Factory\ReflectionFactory;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Layout plugin manager.
 */
class LayoutManager extends PluginManagerBase {

  protected $defaults = array(
    'class' => 'Drupal\layout\Plugin\Layout\StaticLayout',
  );

  /**
   * Overrides Drupal\Component\Plugin\PluginManagerBase::__construct().
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces) {
    // Create layout plugin derivatives from declaratively defined layouts.
    $this->discovery = new AnnotatedClassDiscovery($root_namespaces, 'Layout');
    $this->discovery = new DerivativeDiscoveryDecorator($this->discovery);
    $this->discovery = new ProcessDecorator($this->discovery, array($this, 'processDefinition'));

    $this->factory = new ReflectionFactory($this->discovery);
  }
}
