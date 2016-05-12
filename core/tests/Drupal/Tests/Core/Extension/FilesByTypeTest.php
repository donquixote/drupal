<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFiles_StaticGrep;
use Drupal\Core\Extension\FilesByType\FilesByType_FromFilesGrouped;
use Drupal\Core\Extension\FilesToTypes\FilesToTypes_Static;
use Drupal\Core\Extension\ProfileName\ProfileName_Static;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGrouped_Common;
use Drupal\Tests\UnitTestCase;

class FilesByTypeTest extends UnitTestCase {

  /**
   * Tests the files-by-name discovery with fake directory scans.
   */
  public function testFilesByTypeVirtual() {

    $files = [
      // Override the core instance of the 'minimal' profile.
      'sites/default/profiles/minimal/minimal.info.yml',
      'core/profiles/standard/standard.info.yml',
      'core/profiles/minimal/minimal.info.yml',
      'profiles/myprofile/myprofile.info.yml',
      'profiles/myprofile/modules/myprofile_nested_module/myprofile_nested_module.info.yml',
      'profiles/otherprofile/otherprofile.info.yml',
      'profiles/otherprofile/modules/otherprofile_nested_module/otherprofile_nested_module.info.yml',
      'core/modules/system/system.info.yml',
      'core/themes/seven/seven.info.yml',
      // Override the core instance of the 'seven' theme.
      'sites/default/themes/seven/seven.info.yml',
      'modules/devel/devel.info.yml',
      'modules/poorly_placed_theme/poorly_placed_theme.info.yml',
    ];
    $directoryToFiles = new DirectoryToFiles_StaticGrep($files);

    $types_by_name = [
      'core/themes/seven/seven.info.yml' => 'theme',
      'sites/default/themes/seven/seven.info.yml' => 'theme',
      'core/profiles/standard/standard.info.yml' => 'profile',
      'profiles/myprofile/myprofile.info.yml' => 'profile',
      'profiles/otherprofile/otherprofile.info.yml' => 'profile',
      'modules/poorly_placed_theme/poorly_placed_theme.info.yml' => 'theme',
      'core/profiles/minimal/minimal.info.yml' => 'profile',
      'sites/default/profiles/minimal/minimal.info.yml' => 'profile',
    ];
    $filesToTypes = new FilesToTypes_Static($types_by_name, 'module');

    $filesByType = new FilesByType_FromFilesGrouped(
      new SearchdirPrefixes_Common('sites/default', FALSE),
      SearchdirToFilesGrouped_Common::createFromComponents($directoryToFiles, $filesToTypes),
      new ProfileName_Static('myprofile'));

    $files_by_type_and_name = $filesByType->getFilesByType();

    // @todo Also test order of extensions?
    $this->assertArrayEquals(
      [
        'profile' => [
          'standard' => 'core/profiles/standard/standard.info.yml',
          'myprofile' => 'profiles/myprofile/myprofile.info.yml',
          'minimal' => 'sites/default/profiles/minimal/minimal.info.yml',
          'otherprofile' => 'profiles/otherprofile/otherprofile.info.yml',
        ],
        'module' => [
          'system' => 'core/modules/system/system.info.yml',
          'devel' => 'modules/devel/devel.info.yml',
          'myprofile_nested_module' => 'profiles/myprofile/modules/myprofile_nested_module/myprofile_nested_module.info.yml',
          // The active profile will be listed as a module later, but not here.
          # 'myprofile' => 'profiles/myprofile/myprofile.info.yml',
        ],
        'theme' => [
          'seven' => 'sites/default/themes/seven/seven.info.yml',
          'poorly_placed_theme' => 'modules/poorly_placed_theme/poorly_placed_theme.info.yml',
        ],
      ],
      $files_by_type_and_name);
  }

  /**
   * Tests the files-by-name discovery with real directory scans.
   */
  public function testFilesByTypeReal() {

    // @todo Use custom searchdir prefixes, and omit non-core prefixes.
    $root = \Drupal::staticRoot();

    $filesByType = new FilesByType_FromFilesGrouped(
      new SearchdirPrefixes_Common('sites/default', FALSE),
      SearchdirToFilesGrouped_Common::createSimpleFromRootPath($root),
      new ProfileName_Static('myprofile'));

    $files_by_type_and_name = $filesByType->getFilesByType();

    $expected_subset = [
      'profile' => [
        'standard' => 'core/profiles/standard/standard.info.yml',
        'minimal' => 'core/profiles/minimal/minimal.info.yml',
      ],
      'module' => [
        'system' => 'core/modules/system/system.info.yml',
        'devel' => 'modules/devel/devel.info.yml',
        // The active profile will be listed as a module later, but not here.
        # 'standard' => 'core/profiles/standard/standard.info.yml',
      ],
      'theme' => [
        'seven' => 'core/themes/seven/seven.info.yml',
      ],
    ];

    foreach ($expected_subset as $type => $expected_files_by_name) {
      // @todo Also test order of extensions?
      static::assertArraySubset(
        $expected_files_by_name,
        $files_by_type_and_name[$type]);
    }
  }

}
