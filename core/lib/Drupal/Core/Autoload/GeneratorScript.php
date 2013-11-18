<?php

/**
 * @file
 * Contains \Drupal\Core\Plugin\Discovery\GeneratorScripts.
 *
 * This file is only ever included from Composer commands. This is why it can
 * use the class Composer\Script\Event, even though this class is not part of
 * Drupal core, or any vendor libraries shipped with Drupal.
 */

namespace Drupal\Core\Autoload {

use /** @noinspection PhpUndefinedClassInspection */
  Composer\Script\Event;

  /**
   * Script to be called after Composer generated the autoload files.
   */
  class GeneratorScript {

    /**
     * Replaces the core/vendor/autoload.php with a Drupal-specific autoload.php.
     *
     * Registered as a post-autoload-dump callback in composer.json, to be run
     * every time after Composer has generated the autoload files.
     *
     * @param \Composer\Script\Event $event
     *   Event object provided by Composer.
     */
    public static function postAutoloadDump(Event $event) {
      $core_dir = dirname(dirname(dirname(dirname(__DIR__))));
      // Replace the generated core/vendor/autoload.php.
      copy($core_dir . '/autoload.drupal.php', $core_dir . '/vendor/autoload.php');
    }
  }
}
