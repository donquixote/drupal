<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6User.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing the upload migration.
 */
class Drupal6UploadInstance implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->setModuleVersion('upload', 6000);
    $dbWrapper->createTable('node_type');
    $dbWrapper->getConnection()->merge('node_type')
      ->key(array('type' => 'page'))
      ->fields(array(
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
    ->execute();
  $dbWrapper->getConnection()->merge('node_type')
    ->key(array('type' => 'story'))
    ->fields(array(
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
  $dbWrapper->getConnection()->merge('node_type')
    ->key(array('type' => 'article'))
    ->fields(array(
      'name' => 'Article',
      'module' => 'node',
      'description' => "An <em>article</em>, content type.",
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
    $dbWrapper->createTable('variable');
    $dbWrapper->getConnection()->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'upload_page',
      'value' => 'b:1;',
    ))
    ->values(array(
      'name' => 'upload_story',
      'value' => 'b:1;',
    ))
    ->values(array(
      'name' => 'upload_article',
      'value' => 'b:0;',
    ))
    ->execute();
  }

}
