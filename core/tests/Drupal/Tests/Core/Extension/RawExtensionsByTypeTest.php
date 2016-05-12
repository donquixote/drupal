<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesSingleton;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\ProfileName\ProfileName_Static;
use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByType_FromRawExtensionsGrouped;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedSingleton;
use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedSingleton;
use Drupal\Tests\UnitTestCase;

class RawExtensionsByTypeTest extends UnitTestCase {

  public function testRawExtensionsByType() {

    $root = \Drupal::staticRoot();

    $rawExtensionsByType = RawExtensionsByType_FromRawExtensionsGrouped::create(
      new SearchdirPrefixes_Common('sites/default', FALSE),
      SearchdirToRawExtensionsGroupedSingleton::createInstance(
        $root,
        SearchdirToFilesGroupedSingleton::createInstance(
          $root,
          DirectoryToFilesSingleton::createInstance($root, FALSE))),
      new ProfileName_Static('standard'));

    $extensions_actual = $rawExtensionsByType->getRawExtensionsByType();

    $discovery = new ExtensionDiscovery($root, FALSE, NULL, 'sites/default');

    $extensions_expected = [];
    foreach (['profile', 'module', 'theme', 'theme_engine'] as $type) {
      $extensions_expected[$type] = $discovery->scan($type, FALSE);
      if ($type === 'profile') {
        $discovery->setProfileDirectories(['standard' => 'core/profiles/standard']);
      }
    }

    static::assertArrayHasKey('testing_multilingual_with_english', $extensions_expected['profile']);
    static::assertArrayHasKey('system', $extensions_expected['module']);

    $extension_to_file = function(Extension $extension) {
      return $extension->getPathname();
    };
    $extensions_to_files = function(array $extensions) use ($extension_to_file) {
      return array_map($extension_to_file, $extensions);
    };
    $files_actual = array_map($extensions_to_files, $extensions_actual);
    $files_expected = array_map($extensions_to_files, $extensions_expected);

    $this->assertArrayEquals($files_expected, $files_actual);
  }

}
