<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFiles_Readdir;
use Drupal\Tests\UnitTestCase;

class DirectoryToFilesTest extends UnitTestCase {
  
  public function testDirectoryToFilesReaddir() {
    $root = dirname(dirname(__DIR__));
    $searchdirToFiles = DirectoryToFiles_Readdir::create($root);

    $this->assertArrayEquals(
      [
        'Core/Extension/modules/module_handler_test/module_handler_test.info.yml',
        'Core/Extension/modules/module_handler_test_added/module_handler_test_added.info.yml',
        'Core/Extension/modules/module_handler_test_all1/module_handler_test_all1.info.yml',
        'Core/Extension/modules/module_handler_test_all2/module_handler_test_all2.info.yml',
        'Core/Extension/modules/module_handler_test_no_hook/module_handler_test_no_hook.info.yml',
      ],
      $searchdirToFiles->getFiles('Core/Extension'));
  }

}
