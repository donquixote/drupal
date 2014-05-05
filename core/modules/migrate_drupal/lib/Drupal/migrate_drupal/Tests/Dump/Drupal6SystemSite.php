<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6SystemSite.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\migrate_drupal\Tests\d6\Drupal6DbWrapper;

/**
 * Database dump for testing system.site.yml migration.
 */
class Drupal6SystemSite implements DumpInterface {

  /**
   * {@inheritdoc}
   */
  public function load(Drupal6DbWrapper $dbWrapper) {
    $dbWrapper->createTable('variable');
    $dbWrapper->getConnection()->insert('variable')->fields(array(
      'name',
      'value',
    ))
    ->values(array(
      'name' => 'site_name',
      'value' => 's:9:"site_name";',
    ))
    ->values(array(
      'name' => 'site_mail',
      'value' => 's:21:"site_mail@example.com";',
    ))
    ->values(array(
      'name' => 'site_slogan',
      'value' => serialize('Migrate rocks'),
    ))
    ->values(array(
      'name' => 'site_frontpage',
      'value' => 's:4:"node";',
    ))
    ->values(array(
      'name' => 'site_403',
      'value' => serialize('user'),
    ))
    ->values(array(
      'name' => 'site_404',
      'value' => 's:14:"page-not-found";',
    ))
    ->values(array(
      'name' => 'admin_compact_mode',
      'value' => serialize(FALSE),
    ))
    ->execute();
  }
}
