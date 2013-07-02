<?php

/**
 * @file
 * Contains Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Component\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;

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

    // Register the namespaces of classes that can be used for annotations.
    AnnotationRegistry::registerAutoloadNamespaces($this->getAnnotationNamespaces());

    // The discovery engine knows about namespace-directory mappings that are
    // relevant for plugin discovery.
    // It does not know the exact plugin directories.
    $discovery = $this->buildDiscoveryEngine();

    // Scan namespaces.
    $discoveryAPI = new KrautoloadDiscoveryAPI($this->pluginDefinitionAnnotationName);
    $discovery->apiScanNamespaces($discoveryAPI, array_keys($this->getPluginNamespaces()), FALSE);
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

  /**
   * Build the discovery engine, and build relevant namespaces.
   * @todo Have this stuff properly injected.
   */
  protected function buildDiscoveryEngine() {
    $discovery = new \Krautoload\ApiClassDiscovery_Pluggable();
    $registration = new \Krautoload\RegistrationHub($discovery);
    $modules = \Drupal::getContainer()->get('module_handler')->getModuleList();
    foreach ($modules as $module => $module_file) {
      $module_dir = dirname($module_file);
      $registration->namespacePSRX('Drupal\\' . $module, $module_dir . '/lib/Drupal/' . $module);
      $registration->namespacePSRX('Drupal\\' . $module, $module_dir . '/src');
    }
    $registration->namespacePSRX('Drupal\\Core', DRUPAL_ROOT . '/core/lib/Drupal/Core');
    $registration->namespacePSRX('Drupal\\Core', DRUPAL_ROOT . '/core/src');
    return $discovery;
  }
}
