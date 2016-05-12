<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Tests\UnitTestCase;

class ExtensionDiscoveryTest extends UnitTestCase {

  public function testExtensionDiscoveryReal() {

    $root = \Drupal::staticRoot();

    ExtensionDiscovery::staticReset();

    $discovery = new ExtensionDiscovery($root, FALSE, NULL, 'sites/default');

    /** @var \Drupal\Core\Extension\Extension[][] $extensions_by_type */
    $extensions_by_type = [];
    $files_by_type = [];
    foreach (['profile', 'module', 'theme', 'theme_engine'] as $type) {
      $extensions_by_type[$type] = $discovery->scan($type, FALSE);
      foreach ($extensions_by_type[$type] as $name => $extension) {
        $files_by_type[$type][$name] = $extension->getPathname();
      }
      if ($type === 'profile') {
        // Set profile directories for discovery of the other extension types.
        $discovery->setProfileDirectories(['standard' => 'core/profiles/standard']);
      }
    }

    foreach ([
      'profile' => [
        'minimal' => 'core/profiles/minimal/minimal.info.yml',
        'standard' => 'core/profiles/standard/standard.info.yml',
        'testing' => 'core/profiles/testing/testing.info.yml',
      ],
      'module' => [
        'user' => 'core/modules/user/user.info.yml',
        'field_ui' => 'core/modules/field_ui/field_ui.info.yml',
        'file' => 'core/modules/file/file.info.yml',
        'filter' => 'core/modules/filter/filter.info.yml',
      ],
      'theme' => [
        'bartik' => 'core/themes/bartik/bartik.info.yml',
        'seven' => 'core/themes/seven/seven.info.yml',
        'stable' => 'core/themes/stable/stable.info.yml',
      ],
      'theme_engine' => [
        'twig' => 'core/themes/engines/twig/twig.info.yml'
      ],
    ] as $type => $expected_files) {
      static::assertArraySubset($expected_files, $files_by_type[$type], TRUE, $type);
    }

    $extension_expected = new Extension($root, 'module', 'core/modules/system/system.info.yml', 'system.module');
    $extension_expected->subpath = 'modules/system';
    $extension_expected->origin = 'core';
    static::assertEquals($extension_expected, $extensions_by_type['module']['system'], 'system');

    $extension_expected = new Extension($root, 'theme_engine', 'core/themes/engines/twig/twig.info.yml', 'twig.engine');
    $extension_expected->subpath = 'themes/engines/twig';
    $extension_expected->origin = 'core';
    static::assertEquals($extension_expected, $extensions_by_type['theme_engine']['twig'], 'twig');
  }

}
