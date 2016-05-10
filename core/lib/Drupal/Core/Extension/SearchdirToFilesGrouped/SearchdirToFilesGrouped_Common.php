<?php

namespace Drupal\Core\Extension\SearchdirToFilesGrouped;

use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFiles_Readdir;
use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface;
use Drupal\Core\Extension\FilesToTypes\FilesToTypes_Regex;
use Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface;

class SearchdirToFilesGrouped_Common implements SearchdirToFilesGroupedInterface {

  /**
   * @var string[]
   */
  private $subdirNames;

  /**
   * @var \Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface
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
   * @param bool $include_tests
   * @param bool $buffered
   *
   * @return \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface
   */
  public static function createSimpleFromRootPath($root, $include_tests = FALSE, $buffered = TRUE) {
    $directoryToFiles = DirectoryToFiles_Readdir::create($root, $include_tests, $buffered);
    $filesToTypes = new FilesToTypes_Regex($root);
    return self::createFromComponents($directoryToFiles, $filesToTypes, $buffered);
  }

  /**
   * @param \Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface $searchdirToFiles
   * @param \Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface $filesToTypes
   * @param bool $buffered
   *
   * @return \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface
   */
  public static function createFromComponents(DirectoryToFilesInterface $searchdirToFiles, FilesToTypesInterface $filesToTypes, $buffered = TRUE) {

    $instance = new self(['profiles', 'modules', 'themes'], $searchdirToFiles, $filesToTypes, '.info.yml');
    if ($buffered) {
      $instance = new SearchdirToFilesGrouped_Buffer($instance);
    }

    return $instance;
  }

  /**
   * @param string[] $subdirNames
   *   E.g. ['profiles', 'modules', 'themes']
   * @param \Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface $searchdirToFiles
   * @param \Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface $filesToTypes
   * @param string $suffix
   *   E.g. '.info.yml'
   */
  function __construct(array $subdirNames, DirectoryToFilesInterface $searchdirToFiles, FilesToTypesInterface $filesToTypes, $suffix) {
    $this->subdirNames = $subdirNames;
    $this->searchdirToFiles = $searchdirToFiles;
    $this->filesToTypes = $filesToTypes;
    $this->suffix = $suffix;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilesGrouped($searchdir_prefix) {
    $files_grouped = [];
    foreach ($this->subdirNames as $subdir_name) {
      $searchdir = $searchdir_prefix . $subdir_name;
      $searchdir_files = $this->searchdirToFiles->getFiles($searchdir);
      foreach ($this->filesToTypes->filesGetTypes($searchdir_files) as $file => $type) {
        $name = basename($file, $this->suffix);
        $files_grouped[$type][$subdir_name][$name] = $file;
      }
    }
    return $files_grouped;
  }
}
