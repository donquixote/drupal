<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateTaxonomyVocabularyTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\Dump\Drupal6TaxonomyVocabulary;
use Drupal\migrate_drupal\Tests\MigrateDrupal6TestBase;

/**
 * Tests the Drupal 6 taxonomy vocabularies to Drupal 8 migration.
 */
class MigrateTaxonomyVocabularyTest extends MigrateDrupal6TestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('taxonomy');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate taxonomy vocabularies.',
      'description'  => 'Migrate taxonomy vocabularies to taxonomy.vocabulary.*.yml',
      'group' => 'Migrate Drupal',
    );
  }


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\migrate\Entity\Migration $migration */
    $migration = entity_load('migration', 'd6_taxonomy_vocabulary');
    $this->loadDrupal6Dump(new Drupal6TaxonomyVocabulary());
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
  }

  /**
   * Tests the Drupal 6 taxonomy vocabularies to Drupal 8 migration.
   */
  public function testTaxonomyVocabulary() {
    for ($i = 0; $i < 3; $i++) {
      $j = $i + 1;
      $vocabulary = entity_load('taxonomy_vocabulary', "vocabulary_{$j}_i_{$i}_");
      $this->assertEqual(array($vocabulary->id()), entity_load('migration', 'd6_taxonomy_vocabulary')->getIdMap()->lookupDestinationID(array($j)));
      $this->assertEqual($vocabulary->name, "vocabulary $j (i=$i)");
      $this->assertEqual($vocabulary->description, "description of vocabulary $j (i=$i)");
      $this->assertEqual($vocabulary->hierarchy, $i);
      $this->assertEqual($vocabulary->weight, 4   + $i);
    }
  }

}
