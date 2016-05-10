<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Core\Extension\FilesToTypes\FilesToTypes_Static;
use Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGrouped_Common;
use Drupal\Core\Extension\SearchdirToFiles\SearchdirToFiles_StaticGrep;
use Drupal\Tests\UnitTestCase;

class SearchdirPrefixToFilesGroupedTest extends UnitTestCase {

  public function testSearchdirPrefixToFilesGrouped() {

    $extension_files = [
      'core/profiles/standard/standard.info.yml',
      'core/modules/system/system.info.yml',
      'core/themes/seven/seven.info.yml',
      'modules/devel/devel.info.yml',
    ];
    $searchdirToFiles = new SearchdirToFiles_StaticGrep($extension_files);

    $extension_types = [
      'core/profiles/standard/standard.info.yml' => 'profile',
    ];
    $filesToTypes = new FilesToTypes_Static($extension_types, 'module');

    $searchdirPrefixToFilesGrouped = SearchdirPrefixToFilesGrouped_Common::createFromComponents($searchdirToFiles, $filesToTypes);

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
      $searchdirPrefixToFilesGrouped->searchdirPrefixGetFilesGrouped('core/'));
  }

}
