<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateUploadEntityDisplayTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6UploadInstance;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests the Drupal 6 upload settings to Drupal 8 entity display migration.
 */
class MigrateUploadEntityDisplayTest extends MigrateDrupal6TestBase {

  /**
   * The modules to be enabled during the test.
   *
   * @var array
   */
  static $modules = array('node', 'file');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate upload entity display.',
      'description'  => 'Upload entity display',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    entity_create('node_type', array('type' => 'article'))->save();
    entity_create('node_type', array('type' => 'story'))->save();
    entity_create('node_type', array('type' => 'page'))->save();

    $id_mappings = array(
      'd6_upload_field_instance' => array(
        array(array(1), array('node', 'page', 'upload')),
      ),
    );
    $this->prepareIdMappings($id_mappings);

    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_upload_entity_display');
    $this->loadDrupal6Dump(new Drupal6UploadInstance());
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests the Drupal 6 upload settings to Drupal 8 entity display migration.
   */
  public function testUploadEntityDisplay() {
    $display = entity_get_display('node', 'page', 'default');
    $component = $display->getComponent('upload');
    $this->assertEqual($component['type'], 'file_default');

    $display = entity_get_display('node', 'story', 'default');
    $component = $display->getComponent('upload');
    $this->assertEqual($component['type'], 'file_default');

    // Assure this doesn't exist.
    $display = entity_get_display('node', 'article', 'default');
    $component = $display->getComponent('upload');
    $this->assertTrue(is_null($component));

    $this->assertEqual(array('node', 'page', 'default', 'upload'), entity_load('migration', 'd6_upload_entity_display')->getIdMap()->lookupDestinationID(array('page')));
  }

}
