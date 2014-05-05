<?php
/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6VocabularyField.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing vocabulary to field migration.
 */
class Drupal6VocabularyField implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {

    $dbWrapper->ensureTable('vocabulary', array(
      'fields' => array(
        'vid' => array(
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'name' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'description' => array(
          'type' => 'text',
          'not null' => FALSE,
          'size' => 'big',
        ),
        'help' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'relations' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'hierarchy' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'multiple' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'required' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'tags' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'module' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'weight' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
      ),
      'primary key' => array(
        'vid',
      ),
      'indexes' => array(
        'list' => array(
          'weight',
          'name',
        ),
      ),
      'module' => 'taxonomy',
      'name' => 'vocabulary',
    ));

    $dbWrapper->getConnection()->insert('vocabulary')
      ->fields(array(
        'vid' => 4,
        'name' => 'Tags',
        'description' => 'Tags Vocabulary',
        'help' => '',
        'relations' => '1',
        'hierarchy' => '0',
        'multiple' => '0',
        'required' => '0',
        'tags' => '0',
        'module' => 'taxonomy',
        'weight' => '0',
      ))
      ->execute();


    $dbWrapper->ensureTable('vocabulary_node_types', array(
      'description' => 'Stores which node types vocabularies may be used with.',
      'fields' => array(
        'vid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Primary Key: the {vocabulary}.vid of the vocabulary.',
        ),
        'type' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The {node}.type of the node type for which the vocabulary may be used.',
        ),
      ),
      'primary key' => array('type', 'vid'),
      'indexes' => array(
        'vid' => array('vid'),
      ),
    ));

    $dbWrapper->getConnection()->insert('vocabulary_node_types')->fields(array(
      'vid',
      'type',
    ))
    ->values(array(
      'vid' => '1',
      'type' => 'story',
    ))
    ->values(array(
      'vid' => '2',
      'type' => 'story',
    ))
    ->values(array(
      'vid' => '3',
      'type' => 'story',
    ))
    ->values(array(
      'vid' => '4',
      'type' => 'article',
    ))
    ->values(array(
      'vid' => '4',
      'type' => 'page',
    ))
    ->execute();
    $dbWrapper->setModuleVersion('taxonomy', 6001);
  }

}
