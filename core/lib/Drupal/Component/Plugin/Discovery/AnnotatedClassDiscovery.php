<?php

/**
 * @file
 * Contains Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Component\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Defines a discovery mechanism to find annotated plugins in PSR-0 namespaces.
 */
class AnnotatedClassDiscovery implements DiscoveryInterface {

  /**
   * An object containing the base namespaces from which the plugin namespaces
   * are built by appending the namespace suffix.
   *
   * @var SearchableNamespacesInterface
   */
  protected $rootNamespaces;

  /**
   * Suffix to be appended to each base namespace,
   * to obtain the plugin namespaces.
   *
   * @var string
   */
  protected $namespaceSuffix;

  /**
   * The namespaces of classes that can be used as annotations.
   *
   * @var SearchableNamespacesInterface
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
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable base namespaces from which the plugin namespaces are built.
   * @param string $namespace_suffix
   *   Namespace suffix to be appended to each base plugin namespace, to obtain
   *   the plugin namespaces that will be searched for plugin classes.
   * @param string $plugin_definition_annotation_name
   *   The name of the annotation that contains the plugin definition.
   */
  function __construct(SearchableNamespacesInterface $root_namespaces, $namespace_suffix, $plugin_definition_annotation_name) {
    $this->rootNamespaces = $root_namespaces;
    $this->namespaceSuffix = $namespace_suffix;
    // Initialize with an empty collection of annotation namespaces.
    // More namespaces can be added with addAnnotationNamespace().
    $this->annotationNamespaces = $root_namespaces->buildSearchableNamespaces();
    $this->pluginDefinitionAnnotationName = $plugin_definition_annotation_name;
  }

  /**
   * Add an annotation namespace, after the object has been created.
   *
   * @param string $namespace
   *   Annotation namespace to add.
   */
  public function addAnnotationNamespace($namespace) {
    $this->annotationNamespaces->addNamespace($namespace);
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
    $discoveryAPI = new ClassFileVisitorAPI($this->pluginDefinitionAnnotationName);
    $this->getPluginNamespaces()->apiVisitClassFiles($discoveryAPI, FALSE);
    return $discoveryAPI->getDefinitions();
  }

  /**
   * Returns an array of PSR-0 namespaces to search for plugin classes.
   *
   * @return SearchableNamespacesInterface
   */
  protected function getPluginNamespaces() {
    return $this->rootNamespaces->buildFromSuffix('\\' . $this->namespaceSuffix);
  }

  /**
   * Returns a searchable namespace collection to search for annotation classes.
   *
   * @return SearchableNamespacesInterface
   */
  protected function getAnnotationNamespaces() {
    return $this->annotationNamespaces;
  }
}
