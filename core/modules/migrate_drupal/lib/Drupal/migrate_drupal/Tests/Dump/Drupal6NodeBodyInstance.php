<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6NodeBodyInstance.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing field.instance.node.*.body.yml migration.
 */
class Drupal6NodeBodyInstance implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->createTable('variable');
    $dbWrapper->createTable('node_type');
    $dbWrapper->getDbConnection()->insert('node_type')->fields(array(
      'type',
      'name',
      'module',
      'description',
      'help',
      'has_title',
      'title_label',
      'has_body',
      'body_label',
      'min_word_count',
      'custom',
      'modified',
      'locked',
      'orig_type'
    ))
    ->values(array(
      'type' => 'company',
      'name' => 'Company',
      'module' => 'node',
      'description' => 'Company node type',
      'help' => '',
      'has_title' => 1,
      'title_label' => 'Name',
      'has_body' => 1,
      'body_label' => 'Description',
      'min_word_count' => 20,
      'custom' => 0,
      'modified' => 0,
      'locked' => 0,
      'orig_type' => 'company',
    ))
    ->values(array(
      'type' => 'employee',
      'name' => 'Employee',
      'module' => 'node',
      'description' => 'Employee node type',
      'help' => '',
      'has_title' => 1,
      'title_label' => 'Name',
      'has_body' => 1,
      'body_label' => 'Bio',
      'min_word_count' => 20,
      'custom' => 0,
      'modified' => 0,
      'locked' => 0,
      'orig_type' => 'employee',
    ))
    ->values(array(
      'type' => 'sponsor',
      'name' => 'Sponsor',
      'module' => 'node',
      'description' => 'Sponsor node type',
      'help' => '',
      'has_title' => 1,
      'title_label' => 'Name',
      'has_body' => 0,
      'body_label' => 'Body',
      'min_word_count' => 0,
      'custom' => 0,
      'modified' => 0,
      'locked' => 0,
      'orig_type' => '',
    ))
    ->execute();
    $dbWrapper->getDbConnection()->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'teaser_length',
      'value' => 'i:456;',
    ))
    ->values(array(
      'name' => 'node_preview',
      'value' => 'i:0;',
    ))
    ->execute();

  }

}
