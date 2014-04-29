<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateNodeTypeTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\field\Field;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6NodeType;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests Drupal 6 node type to Drupal 8 migration.
 */
class MigrateNodeTypeTest extends MigrateDrupal6TestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Migrate node type to node.type.*.yml',
      'description' => 'Upgrade node types to node.type.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_node_type');
    $this->loadDrupal6Dump(new Drupal6NodeType());
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests Drupal 6 node type to Drupal 8 migration.
   */
  public function testNodeType() {
    $migration = entity_load('migration', 'd6_node_type');
    // Test the test_page content type.
    $node_type_page = entity_load('node_type', 'test_page');
    $this->assertEqual($node_type_page->id(), 'test_page', 'Node type test_page loaded');
    $expected = array(
      'options' => array(
        'status' => TRUE,
        'promote' => TRUE,
        'sticky' => FALSE,
        'revision' => FALSE,
      ),
      'preview' => 1,
      'submitted' => TRUE,
    );

    $this->assertEqual($node_type_page->settings['node'], $expected, 'Node type test_page settings correct.');
    $this->assertEqual(array('test_page'), $migration->getIdMap()->lookupDestinationID(array('test_page')));

    // Test we have a body field.
    $instance = Field::fieldInfo()->getInstance('node', 'test_page', 'body');
    $this->assertEqual($instance->getLabel(), 'Body', 'Body field was found.');

    // Test the test_story content type.
    $node_type_story = entity_load('node_type', 'test_story');
    $this->assertEqual($node_type_story->id(), 'test_story', 'Node type test_story loaded');
    $expected = array(
      'options' => array(
        'status' => TRUE,
        'promote' => TRUE,
        'sticky' => FALSE,
        'revision' => FALSE,
      ),
      'preview' => 1,
      'submitted' => TRUE,
    );
    $this->assertEqual($node_type_page->settings['node'], $expected, 'Node type test_page settings correct.');
    $this->assertEqual(array('test_story'), $migration->getIdMap()->lookupDestinationID(array('test_story')));

    // Test we don't have a body field.
    $instance = Field::fieldInfo()->getInstance('node', 'test_story', 'body');
    $this->assertEqual($instance, NULL, 'No body field found');
  }
}
