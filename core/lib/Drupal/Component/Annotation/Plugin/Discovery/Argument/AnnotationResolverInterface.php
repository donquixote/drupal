<?php


namespace Drupal\Component\Annotation\Plugin\Discovery\Argument;


use vektah\parser_combinator\language\php\annotation\DoctrineAnnotation;

/**
 *
 */
interface AnnotationResolverInterface {

  /**
   * @param DoctrineAnnotation $annotation
   *
   * @return mixed|null
   */
  function resolve(DoctrineAnnotation $annotation);

} 
