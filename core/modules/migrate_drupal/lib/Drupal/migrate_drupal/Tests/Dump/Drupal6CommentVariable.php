<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6CommentVariable.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing comment variables migration.
 */
class Drupal6CommentVariable implements DumpInterface {


  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->ensureTable('node_type', array(
      'fields' => array(
        'type' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
        ),
        'name' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'module' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ),
        'description' => array(
          'type' => 'text',
          'not null' => TRUE,
          'size' => 'medium',
        ),
        'help' => array(
          'type' => 'text',
          'not null' => TRUE,
          'size' => 'medium',
        ),
        'has_title' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'size' => 'tiny',
        ),
        'title_label' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'has_body' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'size' => 'tiny',
        ),
        'body_label' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'min_word_count' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'size' => 'small',
        ),
        'custom' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'modified' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'locked' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
        ),
        'orig_type' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
      ),
      'primary key' => array(
        'type',
      ),
      'module' => 'node',
      'name' => 'node_type',
    ));
    $dbWrapper->getConnection()->insert('node_type')->fields(array(
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
      'orig_type',
    ))
    ->values(array(
      'type' => 'page',
      'name' => 'Page',
      'module' => 'node',
      'description' => "A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site's initial home page.",
      'help' => '',
      'has_title' => '1',
      'title_label' => 'Title',
      'has_body' => '1',
      'body_label' => 'Body',
      'min_word_count' => '0',
      'custom' => '1',
      'modified' => '1',
      'locked' => '0',
      'orig_type' => 'page',
    ))
    ->values(array(
      'type' => 'story',
      'name' => 'Story',
      'module' => 'node',
      'description' => "A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site's initial home page, and provides the ability to post comments.",
      'help' => '',
      'has_title' => '1',
      'title_label' => 'Title',
      'has_body' => '1',
      'body_label' => 'Body',
      'min_word_count' => '0',
      'custom' => '1',
      'modified' => '1',
      'locked' => '0',
      'orig_type' => 'story',
    ))
    ->execute();
    $dbWrapper->variableSetMultiple(array(
      'comment_anonymous_page' => 0,
      'comment_anonymous_story' => 1,
      'comment_controls_page' => '3',
      'comment_controls_story' => '3',
      'comment_default_mode_page' => '4',
      'comment_default_mode_story' => '2',
      'comment_default_order_page' => '1',
      'comment_default_order_story' => '1',
      'comment_default_per_page_page' => '50',
      'comment_default_per_page_story' => '70',
      'comment_form_location_page' => '0',
      'comment_form_location_story' => '0',
      'comment_page' => '0',
      'comment_preview_page' => '1',
      'comment_preview_story' => '0',
      'comment_story' => '2',
      'comment_subject_field_page' => '1',
      'comment_subject_field_story' => '0',
    ));
    $dbWrapper->setModuleVersion('comment', '6001');
  }

}
