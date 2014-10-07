<?php

/**
 * @file
 * Contains \Drupal\Component\Annotation\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Component\Annotation\Plugin\Discovery;

use Drupal\Component\Annotation\AnnotationInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Annotation\Reflection\MockFileFinder;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Reflection\StaticReflectionParser;
use Drupal\Component\Plugin\Discovery\DiscoveryTrait;
use vektah\parser_combinator\exception\ParseException;
use vektah\parser_combinator\language\php\annotation\DoctrineAnnotation;
use vektah\parser_combinator\language\php\annotation\PhpAnnotationParser;

/**
 * Defines a discovery mechanism to find annotated plugins in PSR-0 namespaces.
 */
class AnnotatedClassDiscovery implements DiscoveryInterface {

  use DiscoveryTrait;

  /**
   * The namespaces within which to find plugin classes.
   *
   * @var array
   */
  protected $pluginNamespaces;

  /**
   * The name of the annotation that contains the plugin definition.
   *
   * E.g. "Drupal\filter\Annotation\Filter"
   *
   * The class corresponding to this name must implement
   * \Drupal\Component\Annotation\AnnotationInterface.
   *
   * @var string
   */
  protected $pluginDefinitionAnnotationName;

  /**
   * The short name of the annotation that contains the plugin definition.
   *
   * This is the same as $pluginDefinitionAnnotationName, but without the
   * namespace.
   *
   * E.g. "Filter", instead of "Drupal\filter\Annotation\Filter"
   *
   * @var string
   */
  protected $pluginDefinitionAnnotationShortname;

  /**
   * The doctrine annotation reader.
   *
   * @var \Doctrine\Common\Annotations\Reader
   */
  protected $annotationReader;

  /**
   * @var PhpAnnotationParser
   */
  protected $parser;

  /**
   * @var \Drupal\Component\Annotation\Plugin\Discovery\Argument\ArgumentsResolver
   */
  protected $argumentsResolver;

  /**
   * Constructs an AnnotatedClassDiscovery object.
   *
   * @param array $plugin_namespaces
   *   (optional) An array of namespace that may contain plugin implementations.
   *   Defaults to an empty array.
   * @param string $plugin_definition_annotation_name
   *   (optional) The name of the annotation that contains the plugin definition.
   *   Defaults to 'Drupal\Component\Annotation\Plugin'.
   */
  function __construct($plugin_namespaces = array(), $plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin') {
    $this->pluginNamespaces = $plugin_namespaces;
    $this->pluginDefinitionAnnotationName = $plugin_definition_annotation_name;
    if (FALSE !== $pos = strrpos($plugin_definition_annotation_name, '\\')) {
      $this->pluginDefinitionAnnotationShortname = substr($plugin_definition_annotation_name, $pos + 1);
    }
    else {
      $this->pluginDefinitionAnnotationShortname = $plugin_definition_annotation_name;
    }
  }

  /**
   * Returns the used doctrine annotation reader.
   *
   * @return \Doctrine\Common\Annotations\Reader
   *   The annotation reader.
   */
  protected function getAnnotationReader() {
    if (!isset($this->annotationReader)) {
      $this->annotationReader = new SimpleAnnotationReader();

      // Add the namespaces from the main plugin annotation, like @EntityType.
      $namespace = substr($this->pluginDefinitionAnnotationName, 0, strrpos($this->pluginDefinitionAnnotationName, '\\'));
      $this->annotationReader->addNamespace($namespace);
    }
    return $this->annotationReader;
  }

  /**
   * Implements Drupal\Component\Plugin\Discovery\DiscoveryInterface::getDefinitions().
   */
  public function getDefinitions() {
    $definitions = array();

    // Search for classes within all PSR-0 namespace locations.
    foreach ($this->getPluginNamespaces() as $namespace => $dirs) {
      foreach ($dirs as $dir) {
        if (file_exists($dir)) {
          $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
          );
          foreach ($iterator as $fileinfo) {
            if ($fileinfo->getExtension() == 'php') {
              $sub_path = $iterator->getSubIterator()->getSubPath();
              $sub_path = $sub_path ? str_replace(DIRECTORY_SEPARATOR, '\\', $sub_path) . '\\' : '';
              $class = $namespace . '\\' . $sub_path . $fileinfo->getBasename('.php');

              // The filename is already known, so there is no need to find the
              // file. However, StaticReflectionParser needs a finder, so use a
              // mock version.
              $finder = MockFileFinder::create($fileinfo->getPathName());
              $parser = new StaticReflectionParser($class, $finder, TRUE);
              $docComment = $parser->getReflectionClass()->getDocComment();
              foreach ($this->findClassAnnotations($docComment) as $id => $args) {
                // The arguments may contain things such as "@Translation(..)",
                // which need to be resolved.
                $args = $this->argumentsResolver->resolveArguments($args);
                $annotationClass = $this->pluginDefinitionAnnotationName;

                // @todo Do we still need to instantiate an $annotation object?
                /** @var $annotation \Drupal\Component\Annotation\AnnotationInterface */
                $annotation = new $annotationClass($args);
                $this->prepareAnnotationDefinition($annotation, $class);
                $definitions[$id] = $annotation->get();
              }
            }
          }
        }
      }
    }

    return $definitions;
  }

  /**
   * Extracts doctrine-style annotations from a class doc comment, and returns
   * only those that match $this->pluginDefinitionAnnotationName.
   *
   * @param string $docComment
   *
   * @return array[]
   */
  protected function findClassAnnotations($docComment) {
    $annotations = array();
    try {
      // The parser returns different pieces of the doc comment, some of which
      // may be doctrine annotations.
      $candidates = $this->parser->parseString($docComment);
    }
    catch (ParseException $e) {
      return array();
    }
    foreach ($candidates as $candidate) {
      if (!$candidate instanceof DoctrineAnnotation) {
        // This is some other part of the doc comment, which is not a
        // doctrine annotation.
        continue;
      }
      if ( $candidate->name !== $this->pluginDefinitionAnnotationName
        && $candidate->name !== $this->pluginDefinitionAnnotationShortname
      ) {
        // This is not one of the annotations we are interested in.
        continue;
      }
      // Annotations can define arguments as e,g. @AnnotationName(key = "value")
      $args = $candidate->arguments;
      if (empty($args['id'])) {
        // Required argument 'id' missing.
        continue;
      }
      $id = $args['id'];
      $annotations[$id] = $args;
    }
    return $annotations;
  }

  /**
   * Prepares the annotation definition.
   *
   * @param \Drupal\Component\Annotation\AnnotationInterface $annotation
   *   The annotation derived from the plugin.
   * @param string $class
   *   The class used for the plugin.
   */
  protected function prepareAnnotationDefinition(AnnotationInterface $annotation, $class) {
    $annotation->setClass($class);
  }

  /**
   * Returns an array of PSR-0 namespaces to search for plugin classes.
   */
  protected function getPluginNamespaces() {
    return $this->pluginNamespaces;
  }

}
