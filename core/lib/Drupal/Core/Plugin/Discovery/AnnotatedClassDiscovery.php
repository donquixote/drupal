<?php

/**
 * @file
 * Definition of Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Core\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery as ComponentAnnotatedClassDiscovery;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Defines a discovery mechanism to find annotated plugins in PSR-0 namespaces.
 */
class AnnotatedClassDiscovery extends ComponentAnnotatedClassDiscovery {

  /**
   * Constructs an AnnotatedClassDiscovery object.
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   * @param string $namespace_suffix
   *   Suffix to build plugin namespaces from root namespaces, e.g. 'views\filter'.
   *   The resulting plugin namespace will be $root_namespace\Plugin\$namespace_suffix.
   * @param string $plugin_definition_annotation_name
   *   (optional) The name of the annotation that contains the plugin definition.
   *   Defaults to 'Drupal\Component\Annotation\Plugin'.
   */
  function __construct(SearchableNamespacesInterface $root_namespaces, $namespace_suffix, $plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin') {
    parent::__construct($root_namespaces, 'Plugin\\' . $namespace_suffix, $plugin_definition_annotation_name);
    $this->addAnnotationNamespace('Drupal\Component\Annotation');
    $this->addAnnotationNamespace('Drupal\Core\Annotation');
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $definitions = parent::getDefinitions();
    foreach ($definitions as &$definition) {
      // Extract the module name from the class namespace if it's not set.
      if (!isset($definition['provider'])) {
        $definition['provider'] = $this->getProviderFromNamespace($definition['class']);
      }
    }
    return $definitions;
  }

  /**
   * Extracts the provider name from a Drupal namespace.
   *
   * @param string $namespace
   *   The namespace to extract the provider from.
   *
   * @return string|null
   *   The matching provider name, or NULL otherwise.
   */
  protected function getProviderFromNamespace($namespace) {
    preg_match('|^Drupal\\\\(?<provider>[\w]+)\\\\|', $namespace, $matches);

    if (isset($matches['provider'])) {
      return $matches['provider'];
    }

    return NULL;
  }

}
