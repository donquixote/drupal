<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateMenuTest
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_drupal\Tests\Dump\Drupal6Menu;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;
use Drupal\Core\Database\Database;

/**
 * Tests the Drupal 6 menu to Drupal 8 migration.
 */
class MigrateMenuTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate menus',
      'description'  => 'Upgrade menus to system.menu.*.yml',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_menu');
    $this->loadDrupal6Dump(new Drupal6Menu());
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * Tests the Drupal 6 menu to Drupal 8 migration.
   */
  public function testMenu() {
    $navigation_menu = entity_load('menu', 'navigation');
    $this->assertEqual($navigation_menu->id(), 'navigation');
    $this->assertEqual($navigation_menu->label(), 'Navigation');
    $this->assertEqual($navigation_menu->description , 'The navigation menu is provided by Drupal and is the main interactive menu for any site. It is usually the only menu that contains personalized links for authenticated users, and is often not even visible to anonymous users.');

    // Test that we can re-import using the ConfigEntityBase destination.
    Database::getConnection('default', 'migrate')
      ->update('menu_custom')
      ->fields(array('title' => 'Home Navigation'))
      ->condition('menu_name', 'navigation')
      ->execute();

    db_truncate(entity_load('migration', 'd6_menu')->getIdMap()->mapTableName())->execute();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load_unchanged('migration', 'd6_menu');
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $navigation_menu = entity_load_unchanged('menu', 'navigation');
    $this->assertEqual($navigation_menu->label(), 'Home Navigation');
  }

}
