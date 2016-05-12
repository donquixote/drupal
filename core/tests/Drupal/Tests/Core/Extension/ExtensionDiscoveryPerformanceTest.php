<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Tests\UnitTestCase;

abstract class ExtensionDiscoveryPerformanceTest extends UnitTestCase {

  public function testExtensionDiscoveryPerformance() {

    $root = \Drupal::staticRoot();

    $t0 = microtime(TRUE);

    $n = 100;

    for ($i = 0; $i < $n; ++$i) {

      ExtensionDiscovery::staticReset();

      $discovery = new ExtensionDiscovery($root, FALSE, NULL, 'sites/default');

      $extensions_expected = [];
      foreach (['profile', 'module', 'theme', 'theme_engine'] as $type) {
        $extensions_expected[$type] = $discovery->scan($type, FALSE);
        if ($type === 'profile') {
          $discovery->setProfileDirectories(['standard' => 'core/profiles/standard']);
        }
      }
    }

    $dt = microtime(TRUE) - $t0;

    static::assertEquals(0, $dt * 1000 / $n . 'ms / loop');
  }

}
