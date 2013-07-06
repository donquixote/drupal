<?php

/**
 * @file
 * Contains Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Component\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Krautoload\SearchableNamespaces_Interface as SearchableNamespacesInterface;

/**
 * Defines a discovery mechanism to find annotated plugins in PSR-0 namespaces.
 */
class AnnotatedClassDiscovery implements DiscoveryInterface {

  /**
   * The namespaces within which to find plugin classes.
   *
   * @var array
   */
  protected $pluginNamespaces;

  /**
   * The namespaces of classes that can be used as annotations.
   *
   * @var array
   */
  protected $annotationNamespaces;

  /**
   * The name of the annotation that contains the plugin definition.
   *
   * The class corresponding to this name must implement
   * \Drupal\Component\Annotation\AnnotationInterface.
   *
   * @var string
   */
  protected $pluginDefinitionAnnotationName;

  /**
   * Constructs an AnnotatedClassDiscovery object.
   *
   * @param array $plugin_namespaces
   *   (optional) An array of namespace that may contain plugin implementations.
   *   Defaults to an empty array.
   * @param array $annotation_namespaces
   *   (optional) The namespaces of classes that can be used as annotations.
   *   Defaults to an empty array.
   * @param string $plugin_definition_annotation_name
   *   (optional) The name of the annotation that contains the plugin definition.
   *   Defaults to 'Drupal\Component\Annotation\Plugin'.
   */
  function __construct(SearchableNamespacesInterface $plugin_namespaces, SearchableNamespacesInterface $annotation_namespaces, $plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin') {
    $this->pluginNamespaces = $plugin_namespaces;
    $this->annotationNamespaces = $annotation_namespaces;
    $this->pluginDefinitionAnnotationName = $plugin_definition_annotation_name;
  }

  /**
   * Implements Drupal\Component\Plugin\Discovery\DiscoveryInterface::getDefinition().
   */
  public function getDefinition($plugin_id) {
    $plugins = $this->getDefinitions();
    return isset($plugins[$plugin_id]) ? $plugins[$plugin_id] : NULL;
  }

  /**
   * Implements Drupal\Component\Plugin\Discovery\DiscoveryInterface::getDefinitions().
   */
  public function getDefinitions() {

    // Register the namespaces of classes that can be used for annotations.
    AnnotationRegistry::reset();
    AnnotationRegistry::registerLoader(array($this->getAnnotationNamespaces(), 'classExistsInNamespaces'));

    // Scan namespaces.
    $discoveryAPI = new ClassFileVisitorAPI($this->pluginDefinitionAnnotationName, $this->getAnnotationNamespaces());
    $this->getPluginNamespaces()->apiVisitClassFiles($discoveryAPI, FALSE);
    return $discoveryAPI->getDefinitions();
  }

  /**
   * Returns an array of PSR-0 namespaces to search for plugin classes.
   */
  protected function getPluginNamespaces() {
    return $this->pluginNamespaces;
  }

  /**
   * Returns a searchable namespace collection to search for annotation classes.
   */
  protected function getAnnotationNamespaces() {
    return $this->annotationNamespaces;
  }
}
