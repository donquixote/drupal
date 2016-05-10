<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Core\Extension\FilesByName\FilesByNameUtil;
use Drupal\Core\Extension\FilesToTypes\FilesToTypes_Static;
use Drupal\Core\Extension\ProfileName\ProfileName_Static;
use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFiles_StaticGrep;
use Drupal\Tests\UnitTestCase;

class FilesByNameTest extends UnitTestCase {

  /**
   * Tests the files-by-name discovery with fake directory scans.
   */
  public function testFilesByNameVirtual() {

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
    $searchdirToFiles = new DirectoryToFiles_StaticGrep($files);

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

    $providers = FilesByNameUtil::createAllFromBaseComponents(
      'sites/default',
      $searchdirToFiles,
      $filesToTypes,
      new ProfileName_Static('myprofile'));

    $files_by_type_and_name = FilesByNameUtil::providersGetFilesByTypeAndName($providers);

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
        'theme_engine' => [],
      ],
      $files_by_type_and_name);
  }

  /**
   * Tests the files-by-name discovery with real directory scans.
   */
  public function testFilesByNameReal() {

    // @todo Use custom searchdir prefixes, and omit non-core prefixes.
    $root = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))));
    $providers = FilesByNameUtil::createAllFromFixedValues($root, 'sites/default', 'standard');

    $files_by_type_and_name = FilesByNameUtil::providersGetFilesByTypeAndName($providers);

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
