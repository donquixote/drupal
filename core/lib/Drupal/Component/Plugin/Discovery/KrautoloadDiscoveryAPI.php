<?php

/**
 * @file
 * Contains Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Component\Plugin\Discovery;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Reflection\StaticReflectionParser;
use Drupal\Component\Reflection\MockFileFinder;

class KrautoloadDiscoveryAPI extends \Krautoload\DiscoveryAPI_Abstract {

  protected $reader;
  protected $annotationName;
  protected $definitions = array();

  /**
   * Constructs a KrautoloadDiscoveryAPI object.
   *
   * @param string $annotationName
   */
  function __construct($annotationName) {
    $this->reader = new AnnotationReader();
    // Prevent @endlink from being parsed as an annotation.
    $this->reader->addGlobalIgnoredName('endlink');
    $this->annotationName = $annotationName;
  }

  /**
   * Get the array of plugin definitions, after everything is scanned.
   *
   * @return array
   */
  function getDefinitions() {
    return $this->definitions;
  }

  /**
   * The directory scan has found a file,
   * which is expected to define the given class.
   *
   * @param string $file
   * @param array $relativeClassNames
   *   Classes that could be in this file according to PSR-0 mapping.
   *   This array is never empty.
   *   The first class in this array is always the class which has no
   *   underscores after the last namespace separator.
   */
  function fileWithClass($file, $relativeClassName) {
    $this->parseFileWithClass($file, $relativeClassName);
  }

  /**
   * The directory scan has found a file,
   * which may define any or none of the given classes.
   *
   * @param string $file
   * @param array $relativeClassNames
   *   Classes that could be in this file according to PSR-0 mapping.
   *   This array is never empty.
   *   The first class in this array is always the class which has no
   *   underscores after the last namespace separator.
   */
  function fileWithClassCandidates($file, $relativeClassNames) {
    // Only pick the first class, which is the no-underscore version.
    $this->parseFileWithClass($file, $relativeClassNames[0]);
  }

  /**
   * The directory scan has found a file which is expected to define the given
   * class.
   *
   * @param string $file
   * @param string $relativeClassName
   */
  protected function parseFileWithClass($file, $relativeClassName) {
    $class = $this->getClassName($relativeClassName);
    if ($annotation = $this->getClassAnnotation($file, $class)) {
      // AnnotationInterface::get() returns the array definition
      // instead of requiring us to work with the annotation object.
      $definition = $annotation->get();
      $definition['class'] = $class;
      $this->definitions[$definition['id']] = $definition;
    }
  }

  /**
   * Extract the annotation from a class.
   *
   * @param string $file
   * @param string $class
   */
  protected function getClassAnnotation($file, $class) {
    // The filename is already known, so there is no need to find the
    // file. However, StaticReflectionParser needs a finder, so use a
    // mock version.
    $finder = MockFileFinder::create($file);
    $parser = new StaticReflectionParser($class, $finder);
    return $this->reader->getClassAnnotation($parser->getReflectionClass(), $this->annotationName);
  }
}
