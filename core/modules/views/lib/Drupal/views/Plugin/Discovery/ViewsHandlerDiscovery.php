<?php

/**
 * @file
 * Contains \Drupal\views\Plugin\Discovery\ViewsHandlerDiscovery.
 */

namespace Drupal\views\Plugin\Discovery;

use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Krautoload\SearchableNamespaces_Interface as SearchableNamespacesInterface;

/**
 * Defines a discovery mechanism to find Views handlers in PSR-0 namespaces.
 */
class ViewsHandlerDiscovery extends AnnotatedClassDiscovery {

  /**
   * The type of handler being discovered.
   *
   * @var string
   */
  protected $type;

  /**
   * An object containing the namespaces to look for plugin implementations.
   *
   * @var \Traversable
   */
  protected $rootNamespaces;

  /**
   * Constructs a ViewsHandlerDiscovery object.
   *
   * @param string $type
   *   The plugin type, for example filter.
   * @param SearchableNamespacesInterface $root_namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   */
  function __construct($type, SearchableNamespacesInterface $root_namespaces) {
    $this->type = $type;
    $this->rootNamespaces = $root_namespaces;

    $this->pluginNamespaces = $root_namespaces->buildFromSuffix("\\Plugin\\views\\{$type}");
    $this->annotationNamespaces = $root_namespaces->buildFromNamespaces(array('Drupal\Component\Annotation'));

    $this->pluginDefinitionAnnotationName = 'Drupal\Component\Annotation\PluginID';
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    // Add the plugin_type to the definition.
    $definitions = parent::getDefinitions();
    foreach ($definitions as $key => $definition) {
      $definitions[$key]['plugin_type'] = $this->type;
    }
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginNamespaces() {
    return $this->rootNamespaces->buildFromSuffix("\\Plugin\\views\\{$this->type}");
  }

}
