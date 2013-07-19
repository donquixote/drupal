<?php

/**
 * @file
 * Contains \Drupal\Core\Action\ActionManager.
 */

namespace Drupal\Core\Action;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Provides an Action plugin manager.
 *
 * @see \Drupal\Core\Annotation\Operation
 * @see \Drupal\Core\Action\OperationInterface
 */
class ActionManager extends PluginManagerBase {

  /**
   * Constructs a ActionManager object.
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces) {
    $this->discovery = new AnnotatedClassDiscovery($root_namespaces, 'Action', 'Drupal\Core\Annotation\Action');
    $this->discovery = new AlterDecorator($this->discovery, 'action_info');

    $this->factory = new ContainerFactory($this);
  }

  /**
   * Gets the plugin definitions for this entity type.
   *
   * @param string $type
   *   The entity type name.
   *
   * @return array
   *   An array of plugin definitions for this entity type.
   */
  public function getDefinitionsByType($type) {
    return array_filter($this->getDefinitions(), function ($definition) use ($type) {
      return $definition['type'] === $type;
    });
  }

}
