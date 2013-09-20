<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\Discovery\GeneratorScripts.
 */

namespace Drupal\Core\Autoload;

use Composer\Script\Event;

/**
 * Script to be called after composer generated the autoload files.
 */
class GeneratorScript {

  /**
   * Called after composer autoload-dump.
   * Replaces the core/vendor/autoload.php with a Drupal-specific autoload.php.
   *
   * @param \Composer\Script\Event $event
   */
  public static function postAutoloadDump(Event $event) {
    $core_dir = dirname(dirname(dirname(dirname(__DIR__))));
    // Copy Drupal's
    copy($core_dir . '/autoload.drupal.php', $core_dir . '/vendor/autoload.php');
  }
} 