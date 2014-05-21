<?php

/**
 * @file
 * Contains \Drupal\path\Tests\Plugin\Field\FieldType\PathFieldDefinitionTest
 */

namespace Drupal\path\Tests\Field;

use Drupal\Tests\Core\Field\FieldDefinitionTestBase;

/**
 * Tests a field definition for a 'path' field.
 *
 * @see \Drupal\Core\Field\FieldDefinition
 * @see \Drupal\path\Plugin\Field\FieldType\PathItem
 *
 * @group Drupal
 * @group path
 */
class PathFieldDefinitionTest extends FieldDefinitionTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Path field definitions',
      'description' => 'Tests that field definitions for path fields work correctly.',
      'group' => 'Path',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginId() {
    return 'path';
  }

  /**
   * {@inheritdoc}
   */
  protected function getNamespacePath() {
    // @todo Remove this distinction after issue #2247991.
    return is_dir($dir_psr4 = dirname(__DIR__) . '/src')
      // After the PSR-4 transition.
      ? $dir_psr4
      // Before the PSR-4 transition.
      : dirname(dirname(dirname(__DIR__))) . '/lib/Drupal/path';
  }

  /**
   * Tests FieldDefinition::getColumns().
   *
   * @covers \Drupal\Core\Field\FieldDefinition::getColumns
   * @covers \Drupal\path\Plugin\Field\FieldType\PathItem::getSchema
   */
  public function testGetColumns() {
    $this->assertSame(array(), $this->definition->getColumns());
  }

}
