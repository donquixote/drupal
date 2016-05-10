<?php

namespace Drupal\Core\Extension\FilesByName;

use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;
use Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface;

abstract class FilesByNameBase implements FilesByNameInterface {

  /**
   * @var \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface
   */
  private $searchdirPrefixes;

  /**
   * @var \Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface
   */
  private $searchdirToFilesGrouped;

  /**
   * @var string
   */
  private $extensionType;

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface $searchdirToFilesGrouped
   * @param string $extensionType
   */
  function __construct(SearchdirPrefixesInterface $searchdirPrefixes, SearchdirPrefixToFilesGroupedInterface $searchdirToFilesGrouped, $extensionType) {
    $this->searchdirPrefixes = $searchdirPrefixes;
    $this->searchdirToFilesGrouped = $searchdirToFilesGrouped;
    $this->extensionType = $extensionType;
  }

  /**
   * Provides an array of extension file paths by machine name.
   *
   * @return string[]
   *   Format: $['standard'] = 'core/profiles/standard/standard.info.yml'
   */
  public function getFilesByName() {

    $searchdir_prefix_weights = $this->getSearchdirPrefixWeights();
    $files_grouped_by_searchdir_prefix = $this->getFilesGroupedBySearchdirPrefix();

    // Make sure that arrays have same size for array_multisort().
    foreach (array_diff_key($searchdir_prefix_weights, $files_grouped_by_searchdir_prefix) as $searchdir_prefix => $_) {
      // This case might happen if some searchdir prefixes have no entries.
      $files_grouped_by_searchdir_prefix[$searchdir_prefix] = [];
    }
    foreach (array_diff_key($files_grouped_by_searchdir_prefix, $searchdir_prefix_weights) as $searchdir_prefix => $_) {
      // This case should not really happen.
      throw new \RuntimeException("Unexpected searchdir prefix '$searchdir_prefix'.");
    }

    array_multisort($searchdir_prefix_weights, SORT_DESC | SORT_NUMERIC, $files_grouped_by_searchdir_prefix);

    $files_by_name = [];
    foreach ($files_grouped_by_searchdir_prefix as $files_by_subdir_name) {
      foreach ($files_by_subdir_name as $subdir_files_by_name) {
        $files_by_name += $subdir_files_by_name;
      }
    }

    return $files_by_name;
  }

  /**
   * @return int[]
   *   Format: $['core/'] = 0
   */
  protected function getSearchdirPrefixWeights() {
    return $this->searchdirPrefixes->getSearchdirPrefixWeights();
  }

  /**
   * @return string[][][]
   *   Format: $[$searchdir_prefix][$subdir_name][$extension_name] = $file
   *   E.g. $['core/']['modules']['system'] = 'core/modules/system/system.info.yml'
   */
  protected function getFilesGroupedBySearchdirPrefix() {

    $files_grouped = [];
    // Do NOT use $this->getSearchdirPrefixWeights() here, to avoid the possible
    // modifications from a subclass.
    foreach ($this->searchdirPrefixes->getSearchdirPrefixWeights() as $searchdir_prefix => $searchdir_weight) {
      $files_by_type_and_subdir_and_name = $this->searchdirToFilesGrouped->searchdirPrefixGetFilesGrouped($searchdir_prefix);
      $files_grouped[$searchdir_prefix] = array_key_exists($this->extensionType, $files_by_type_and_subdir_and_name)
        ? $files_by_type_and_subdir_and_name[$this->extensionType]
        : [];
    }

    return $files_grouped;
  }

}
