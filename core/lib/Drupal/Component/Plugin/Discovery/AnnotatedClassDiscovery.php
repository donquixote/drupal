<?php

/**
 * @file
 * Contains Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Component\Plugin\Discovery;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * Defines a discovery mechanism to find annotated plugins in PSR-0 namespaces.
 */
class AnnotatedClassDiscovery extends AbstractAnnotatedClassDiscovery {

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
  function __construct($plugin_namespaces = array(), $annotation_namespaces = array(), $plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin') {
    $this->pluginNamespaces = $plugin_namespaces;
    $this->annotationNamespaces = $annotation_namespaces;
    parent::__construct($plugin_definition_annotation_name);
  }

  /**
   * @inheritdoc
   */
  protected function getPluginNamespaces() {
    return $this->pluginNamespaces;
  }

  /**
   * @inheritdoc
   */
  protected function getAnnotationNamespaces() {
    return $this->annotationNamespaces;
  }

}
