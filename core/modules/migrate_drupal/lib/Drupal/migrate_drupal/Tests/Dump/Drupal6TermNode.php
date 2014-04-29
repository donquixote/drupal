<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6TermNode.
 */


namespace Drupal\migrate_drupal\Tests\Dump;


use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

class Drupal6TermNode implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->setModuleVersion('taxonomy', 6000);
    $dbWrapper->createTable('term_node', array(
      'fields' => array(
        'nid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'vid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
        'tid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ),
      ),
      'indexes' => array(
        'vid' => array(
          'vid',
        ),
        'nid' => array(
          'nid',
        ),
      ),
      'primary key' => array(
        'tid',
        'vid',
      ),
      'module' => 'taxonomy',
      'name' => 'term_node',
    ));
    $dbWrapper->getDbConnection()->insert('term_node')->fields(array(
      'nid',
      'vid',
      'tid',
    ))
    ->values(array(
      'nid' => 1,
      'vid' => 1,
      'tid' => 1,
    ))
    ->values(array(
      'nid' => 1,
      'vid' => 2,
      'tid' => 5,
    ))
    ->values(array(
      'nid' => 1,
      'vid' => 2,
      'tid' => 4,
    ))
    ->values(array(
      'nid' => 2,
      'vid' => 3,
      'tid' => 2,
    ))
    ->values(array(
      'nid' => 2,
      'vid' => 3,
      'tid' => 3,
    ))
    ->execute();

  }

}
