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
class KrautoloadDiscoveryAPI implements \Krautoload\DiscoveryAPI_Interface {

  protected $reader;
  protected $annotationName;
  protected $definitions = array();

  function __construct($reader, $annotationName) {
    $this->reader = $reader;
    $this->annotationName = $annotationName;
  }

  function getDefinitions() {
    return $this->definitions;
  }

  function fileWithClass($file, $class) {
    $this->confirmedFileWithClass($file, $class);
  }

  function fileWithClassCandidates($file, $classes) {
    include_once $file;
    // We are only interested in the first class,
    // which is the no-underscore version.
    if (class_exists($classes[0], FALSE)) {
      $this->includedFileWithClass($file, $classes[0]);
    }
  }

  protected function confirmedFileWithClass($file, $class) {
    if ($annotation = $this->getClassAnnotation($file, $class)) {
      // AnnotationInterface::get() returns the array definition
      // instead of requiring us to work with the annotation object.
      $definition = $annotation->get();
      $definition['class'] = $class;
      $this->definitions[$definition['id']] = $definition;
    }
  }

  protected function getClassAnnotation($file, $class) {
    // The filename is already known, so there is no need to find the
    // file. However, StaticReflectionParser needs a finder, so use a
    // mock version.
    $finder = MockFileFinder::create($file);
    $parser = new StaticReflectionParser($class, $finder);
    return $this->reader->getClassAnnotation($parser->getReflectionClass(), $this->annotationName);
  }
}
