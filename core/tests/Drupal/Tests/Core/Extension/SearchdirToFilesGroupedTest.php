<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Core\Extension\FilesToTypes\FilesToTypes_Static;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGrouped_Common;
use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFiles_StaticGrep;
use Drupal\Tests\UnitTestCase;

class SearchdirToFilesGroupedTest extends UnitTestCase {

  public function testSearchdirToFilesGrouped() {

    $extension_files = [
      'core/profiles/standard/standard.info.yml',
      'core/modules/system/system.info.yml',
      'core/themes/seven/seven.info.yml',
      'modules/devel/devel.info.yml',
    ];
    $searchdirToFiles = new DirectoryToFiles_StaticGrep($extension_files);

    $extension_types = [
      'core/profiles/standard/standard.info.yml' => 'profile',
    ];
    $filesToTypes = new FilesToTypes_Static($extension_types, 'module');

    $searchdirToFilesGrouped = SearchdirToFilesGrouped_Common::createFromComponents($searchdirToFiles, $filesToTypes);

    $this->assertArrayEquals(
      [
        'module' => [
          'modules' => [
            'system' => 'core/modules/system/system.info.yml',
          ],
          'themes' => [
            'seven' => 'core/themes/seven/seven.info.yml',
          ],
        ],
        'profile' => [
          'profiles' => [
            'standard' => 'core/profiles/standard/standard.info.yml',
          ],
        ],
      ],
      $searchdirToFilesGrouped->getFilesGrouped('core/'));
  }

}
