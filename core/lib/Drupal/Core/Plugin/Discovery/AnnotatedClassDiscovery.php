<?php

/**
 * @file
 * Definition of Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Core\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\AbstractAnnotatedClassDiscovery;

/**
 * Defines a discovery mechanism to find annotated plugins in PSR-0 namespaces.
 */
class AnnotatedClassDiscovery extends AbstractAnnotatedClassDiscovery {

  /**
   * An object containing the namespaces to look for plugin implementations.
   *
   * @var \Traversable
   */
  protected $rootNamespacesIterator;

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
   * The namespaces of classes that can be used as annotations.
   *
   * @var array
   */
  protected $annotationNamespaces = array();

  /**
   * Constructs an AnnotatedClassDiscovery object.
   *
   * @param \Traversable $root_namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   *   If $subdir is not an empty string, it will be appended to each namespace.
   * @param string $namespace_suffix
   *   Suffix to append to each of the root namespaces, to obtain the plugin
   *   namespaces. E.g. '\Plugin\views\filter', or empty string if plugins are
   *   located at the top level of each of the root namespaces.
   * @param string $plugin_definition_annotation_name
   *   (optional) The name of the annotation that contains the plugin definition.
   *   Defaults to 'Drupal\Component\Annotation\Plugin'.
   */
  public function __construct(\Traversable $root_namespaces, $namespace_suffix = '', $plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin') {
    $this->rootNamespacesIterator = $root_namespaces;
    if ($namespace_suffix) {
      if ($namespace_suffix && '\\' !== $namespace_suffix[0]) {
        $namespace_suffix = '\\' . $namespace_suffix;
      }
      $this->namespaceSuffix = $namespace_suffix;
      $this->directorySuffix = str_replace('\\', '/', $namespace_suffix);
    }
    $this->addAnnotationNamespace('Drupal\Component\Annotation');
    $this->addAnnotationNamespace('Drupal\Core\Annotation');
    parent::__construct($plugin_definition_annotation_name);
  }

  /**
   * @param string $namespace
   *   The namespace.
   * @param string $dir
   *   Optional: The directory.
   * @throws \Exception
   */
  public function addAnnotationNamespace($namespace, $dir = NULL) {

    if (!empty($dir)) {
      $this->annotationNamespaces[$namespace] = $dir;
      return;
    }

    if ('Drupal\Core\\' === substr($namespace, 0, 12)) {
      $this->annotationNamespaces[$namespace] = DRUPAL_ROOT . '/core/lib/' . str_replace('\\', '/', $namespace);
      return;
    }

    if ('Drupal\Component\\' === substr($namespace, 0, 17)) {
      $this->annotationNamespaces[$namespace] = DRUPAL_ROOT . '/core/lib/' . str_replace('\\', '/', $namespace);
      return;
    }

    if (!empty($this->rootNamespacesIterator[$namespace])) {
      $this->annotationNamespaces[$namespace] = $this->rootNamespacesIterator[$namespace];
      return;
    }

    $fragments = explode('\\', $namespace);
    $relativePath = array_pop($fragments);
    while (!empty($fragments)) {
      $prefix = implode('\\', $fragments);
      if (!empty($this->rootNamespacesIterator[$prefix])) {
        $this->annotationNamespaces[$namespace] = $this->rootNamespacesIterator[$prefix] . '/' . $relativePath;
        return;
      }
      $relativePath = array_pop($fragments) . '/' . $relativePath;
    }

    throw new \Exception("Unable to find base directory for annotation namespace '$namespace'.");
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

  /**
   * @inheritdoc
   */
  protected function getAnnotationNamespaces() {
    return $this->annotationNamespaces;
  }

}
