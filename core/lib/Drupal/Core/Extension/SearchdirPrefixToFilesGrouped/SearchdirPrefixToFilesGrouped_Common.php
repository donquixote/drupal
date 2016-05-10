<?php

namespace Drupal\Core\Extension\SearchdirPrefixToFilesGrouped;

use Drupal\Core\Extension\FilesToTypes\FilesToTypes_Regex;
use Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface;
use Drupal\Core\Extension\SearchdirToFiles\SearchdirToFiles_Readdir;
use Drupal\Core\Extension\SearchdirToFiles\SearchdirToFilesInterface;

class SearchdirPrefixToFilesGrouped_Common implements SearchdirPrefixToFilesGroupedInterface {

  /**
   * @var string[]
   */
  private $subdirNames;

  /**
   * @var \Drupal\Core\Extension\SearchdirToFiles\SearchdirToFilesInterface
   */
  private $searchdirToFiles;

  /**
   * @var \Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface
   */
  private $filesToTypes;

  /**
   * @var string
   */
  private $suffix;

  /**
   * @param string $root
   *
   * @return \Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface
   */
  public static function createSimpleFromRootPath($root) {
    $searchdirToFiles = SearchdirToFiles_Readdir::create($root);
    $filesToTypes = new FilesToTypes_Regex($root);
    return self::createFromComponents($searchdirToFiles, $filesToTypes);
  }

  /**
   * @param \Drupal\Core\Extension\SearchdirToFiles\SearchdirToFilesInterface $searchdirToFiles
   * @param \Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface $filesToTypes
   *
   * @return \Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface
   */
  public static function createFromComponents(SearchdirToFilesInterface $searchdirToFiles, FilesToTypesInterface $filesToTypes) {
    return new self(
      ['profiles', 'modules', 'themes'],
      $searchdirToFiles,
      $filesToTypes,
      '.info.yml');
  }

  /**
   * @param string[] $subdirNames
   *   E.g. ['profiles', 'modules', 'themes']
   * @param \Drupal\Core\Extension\SearchdirToFiles\SearchdirToFilesInterface $searchdirToFiles
   * @param \Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface $filesToTypes
   * @param string $suffix
   *   E.g. '.info.yml'
   */
  function __construct(array $subdirNames, SearchdirToFilesInterface $searchdirToFiles, FilesToTypesInterface $filesToTypes, $suffix) {
    $this->subdirNames = $subdirNames;
    $this->searchdirToFiles = $searchdirToFiles;
    $this->filesToTypes = $filesToTypes;
    $this->suffix = $suffix;
  }

  /**
   * @param string $searchdir_prefix
   *   E.g. 'core/' or 'sites/default/'
   *
   * @return string[][]
   *   Format: $[$extension_type][$subdir_name][$name] = $file
   *   E.g. $['module']['modules']['system'] = 'core/modules/system/system.module'
   */
  public function searchdirPrefixGetFilesGrouped($searchdir_prefix) {
    $files_grouped = [];
    foreach ($this->subdirNames as $subdir_name) {
      $searchdir = $searchdir_prefix . $subdir_name;
      $searchdir_files = $this->searchdirToFiles->searchdirGetFiles($searchdir);
      foreach ($this->filesToTypes->filesGetTypes($searchdir_files) as $file => $type) {
        $name = basename($file, $this->suffix);
        $files_grouped[$type][$subdir_name][$name] = $file;
      }
    }
    return $files_grouped;
  }
}
