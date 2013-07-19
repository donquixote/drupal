<?php

/**
 * @file
 * Contains \Drupal\views\Plugin\Discovery\ViewsHandlerDiscovery.
 */

namespace Drupal\views\Plugin\Discovery;

use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery as CoreAnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery as ComponentAnnotatedClassDiscovery;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Defines a discovery mechanism to find Views handlers in PSR-0 namespaces.
 */
class ViewsHandlerDiscovery extends CoreAnnotatedClassDiscovery {

  /**
   * The type of handler being discovered.
   *
   * @var string
   */
  protected $type;

  /**
   * Constructs a ViewsHandlerDiscovery object.
   *
   * @param string $type
   *   The plugin type, for example filter.
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   */
  function __construct($type, SearchableNamespacesInterface $root_namespaces) {
    $this->type = $type;
    ComponentAnnotatedClassDiscovery::__construct($root_namespaces, 'views\\' . $type, 'Drupal\Component\Annotation\PluginID');
    $this->addAnnotationNamespace('Drupal\Component\Annotation');
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $definitions = parent::getDefinitions();
    // Add the plugin_type to each definition.
    foreach ($definitions as $key => $definition) {
      $definitions[$key]['plugin_type'] = $this->type;
    }
    return $definitions;
  }

}
