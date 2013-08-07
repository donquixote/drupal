<?php

/**
 * @file
 * Definition of Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Core\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery as ComponentAnnotatedClassDiscovery;

/**
 * Defines a discovery mechanism to find annotated plugins in PSR-0 namespaces.
 */
class AnnotatedClassDiscovery extends ComponentAnnotatedClassDiscovery {

  /**
   * The subdirectory within a namespace to look for plugins.
   *
   * If the plugins are in the top level of the namespace and not within a
   * subdirectory, set this to an empty string.
   *
   * @var string
   */
  protected $directorySuffix = '';

  /**
   * The subdirectory within a namespace to look for plugins.
   *
   * If the plugins are in the top level of the namespace and not within a
   * subdirectory, set this to an empty string.
   *
   * @var string
   */
  protected $namespaceSuffix = '';

  /**
   * An object containing the namespaces to look for plugin implementations.
   *
   * @var \Traversable
   */
  protected $rootNamespacesIterator;

  /**
   * Constructs an AnnotatedClassDiscovery object.
   *
   * @param string $subdir
   *   Either the plugin's subdirectory, for example 'Plugin/views/filter', or
   *   empty string if plugins are located at the top level of the namespace.
   * @param \Traversable $root_namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   *   If $subdir is not an empty string, it will be appended to each namespace.
   * @param array $annotation_namespaces
   *   (optional) The namespaces of classes that can be used as annotations.
   *   Defaults to an empty array.
   * @param string $plugin_definition_annotation_name
   *   (optional) The name of the annotation that contains the plugin definition.
   *   Defaults to 'Drupal\Component\Annotation\Plugin'.
   */
  function __construct($subdir, \Traversable $root_namespaces, $annotation_namespaces = array(), $plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin') {
    if ($subdir) {
      if ('/' !== $subdir[0]) {
        $subdir = '/' . $subdir;
      }
      $this->directorySuffix = $subdir;
      $this->namespaceSuffix = str_replace('/', '\\', $subdir);
    }
    $this->rootNamespacesIterator = $root_namespaces;
    $annotation_namespaces += array(
      'Drupal\Component\Annotation' => DRUPAL_ROOT . '/core/lib/Drupal/Component/Annotation',
      'Drupal\Core\Annotation' => DRUPAL_ROOT . '/core/lib/Drupal/Core/Annotation',
    );
    $plugin_namespaces = array();
    parent::__construct($plugin_namespaces, $annotation_namespaces, $plugin_definition_annotation_name);
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

  /**
   * {@inheritdoc}
   */
  protected function getPluginNamespaces() {
    $plugin_namespaces = array();
    if ($this->namespaceSuffix) {
      foreach ($this->rootNamespacesIterator as $namespace => $dirs) {
        $namespace .= $this->namespaceSuffix;
        foreach ((array) $dirs as $dir) {
          $plugin_namespaces[$namespace][] = $dir . $this->directorySuffix;
        }
      }
    }
    else {
      foreach ($this->rootNamespacesIterator as $namespace => $dirs) {
        $plugin_namespaces[$namespace] = (array) $dirs;
      }
    }

    return $plugin_namespaces;
  }

}
