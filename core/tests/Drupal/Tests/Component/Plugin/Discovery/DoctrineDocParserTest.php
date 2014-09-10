<?php

namespace Drupal\Tests\Component\Plugin\Discovery;

use Doctrine\Common\Annotations\DocParser;
use Drupal\Tests\UnitTestCase;

/**
 * Tests specific usage of Doctrine DocParser.
 *
 * @see \Doctrine\Common\Annotations\DocParser
 */
class DoctrineDocParserTest extends UnitTestCase {

  /**
   * Tests parsing of a typical plugin doc comment.
   */
  function testDoctrineDocParser() {
    $docComment = <<<EOT
/**
 * Plugin implementation of the 'link' formatter.
 *
 * @FieldFormatter(
 *   id = "link",
 *   label = @Translation("Link"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
EOT;

    // Trigger the class loader to make @FieldFormatter available.
    $this->assertTrue(class_exists('Drupal\Core\Field\Annotation\FieldFormatter'));
    $this->assertTrue(class_exists('Drupal\Core\Annotation\Translation'));

    $parser = new DocParser();
    $parser->addNamespace('Drupal\Core\Annotation');
    $parser->addNamespace('Drupal\Core\Field\Annotation');
    $annotations = $parser->parse($docComment);
    var_dump($annotations);
  }

} 
