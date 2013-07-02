<?php

/**
 * @file
 * Contains Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Component\Plugin\Discovery;

use DirectoryIterator;
use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Reflection\MockFileFinder;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Reflection\StaticReflectionParser;

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
  function __construct($plugin_namespaces = array(), $annotation_namespaces = array(), $plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin') {
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
    $definitions = array();
    $reader = new AnnotationReader();
    // Prevent @endlink from being parsed as an annotation.
    $reader->addGlobalIgnoredName('endlink');

    // Register the namespaces of classes that can be used for annotations.
    AnnotationRegistry::registerAutoloadNamespaces($this->getAnnotationNamespaces());

    // Build the discovery thingie.
    // @todo Have this stuff properly injected.
    $discovery = new \Krautoload\ApiClassDiscovery_Pluggable();
    $registration = new \Krautoload\RegistrationHub($discovery);
    $modules = \Drupal::getContainer()->get('module_handler')->getModuleList();
    foreach ($modules as $module => $module_file) {
      $module_dir = dirname($module_file);
      $discovery->namespacePSRX('Drupal\\' . $module, $module_dir . '/lib/Drupal/' . $module);
      $discovery->namespacePSRX('Drupal\\' . $module, $module_dir . '/src');
    }
    $discovery->namespacePSRX('Drupal\\Core', DRUPAL_ROOT . '/core/lib/Drupal/Core');
    $discovery->namespacePSRX('Drupal\\Core', DRUPAL_ROOT . '/core/src');

    // Scan namespaces.
    $discoveryAPI = new KrautoloadDiscoveryAPI($reader, $this->pluginDefinitionAnnotationName);
    $discovery->scanNamespaces($discoveryAPI, array_keys($this->getPluginNamespaces()), FALSE);
    return $discoveryAPI->getDefinitions();
  }

  /**
   * Returns an array of PSR-0 namespaces to search for plugin classes.
   */
  protected function getPluginNamespaces() {
    return $this->pluginNamespaces;
  }

  /**
   * Returns an array of PSR-0 namespaces to search for annotation classes.
   */
  protected function getAnnotationNamespaces() {
    return $this->annotationNamespaces;
  }

}
