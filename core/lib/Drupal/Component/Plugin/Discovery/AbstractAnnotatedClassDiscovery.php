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
abstract class AbstractAnnotatedClassDiscovery implements DiscoveryInterface {

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
   * Constructs an AbstractAnnotatedClassDiscovery object.
   *
   * @param string $plugin_definition_annotation_name
   *   (optional) The name of the annotation that contains the plugin definition.
   *   Defaults to 'Drupal\Component\Annotation\Plugin'.
   */
  function __construct($plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin') {
    $this->pluginDefinitionAnnotationName = $plugin_definition_annotation_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition($plugin_id) {
    $plugins = $this->getDefinitions();
    return isset($plugins[$plugin_id]) ? $plugins[$plugin_id] : NULL;
  }

  /**
   * @param string $class
   * @return bool
   */
  public function loadAnnotationClass($class) {

    if (class_exists($class, FALSE)) {
      return TRUE;
    }

    foreach ($this->getAnnotationNamespaces() as $namespace => $dirs) {
      if (0 === strpos($class, $namespace)) {
        if (TRUE === $dirs) {
          // Use the regular class loader.
          return class_exists($class);
        }
        // Treat $dirs as PSR-4 directories.
        $relativePath = str_replace('\\', '/', substr($class, strlen($namespace))) . '.php';
        foreach ((array) $dirs as $dir) {
          if (file_exists($file = $dir . '/' . $relativePath)) {
            require $file;
            return TRUE;
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $definitions = array();
    $reader = new AnnotationReader();
    // Prevent @endlink from being parsed as an annotation.
    $reader->addGlobalIgnoredName('endlink');
    $reader->addGlobalIgnoredName('file');

    // Register the namespaces of classes that can be used for annotations.
    AnnotationRegistry::registerLoader(array($this, 'loadAnnotationClass'));

    // Search for classes within all PSR-0 namespace locations.
    foreach ($this->getPluginNamespaces() as $namespace => $dirs) {
      foreach ($dirs as $dir) {
        if (file_exists($dir)) {
          foreach (new DirectoryIterator($dir) as $fileinfo) {
            // @todo Once core requires 5.3.6, use $fileinfo->getExtension().
            if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
              $class = $namespace . '\\' . $fileinfo->getBasename('.php');

              // The filename is already known, so there is no need to find the
              // file. However, StaticReflectionParser needs a finder, so use a
              // mock version.
              $finder = MockFileFinder::create($fileinfo->getPathName());
              $parser = new StaticReflectionParser($class, $finder);

              if ($annotation = $reader->getClassAnnotation($parser->getReflectionClass(), $this->pluginDefinitionAnnotationName)) {
                // AnnotationInterface::get() returns the array definition
                // instead of requiring us to work with the annotation object.
                $definition = $annotation->get();
                $definition['class'] = $class;
                $definitions[$definition['id']] = $definition;
              }
            }
          }
        }
      }
    }

    // Don't let the loaders pile up.
    AnnotationRegistry::reset();

    return $definitions;
  }

  /**
   * Returns an array of PSR-0 namespaces to search for plugin classes.
   *
   * @return array
   */
  protected abstract function getPluginNamespaces();

  /**
   * Returns an array of PSR-0 namespaces to search for annotation classes.
   *
   * @return array
   */
  protected abstract function getAnnotationNamespaces();

}
