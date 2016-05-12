<?php

namespace Drupal\Core\Extension\SearchdirToRawExtensionsGrouped;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface;

/**
 * Default implementation, building extension objects from a grouped list of
 * *.info.yml files.
 */
class SearchdirToRawExtensionsGrouped_Common implements SearchdirToRawExtensionsGroupedInterface {

  /**
   * @var \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface
   */
  private $searchdirToFilesGrouped;

  /**
   * @var string
   */
  private $root;

  /**
   * @param \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface $searchdirToFilesGrouped
   * @param string $root
   *   The Drupal root directory.
   */
  public function __construct(SearchdirToFilesGroupedInterface $searchdirToFilesGrouped, $root) {
    $this->searchdirToFilesGrouped = $searchdirToFilesGrouped;
    $this->root = $root;
  }

  /**
   * {@inheritdoc}
   */
  public function getRawExtensionsGrouped($searchdir_prefix) {

    $extensions_grouped = [];
    foreach ($this->searchdirToFilesGrouped->getFilesGrouped($searchdir_prefix) as $type => $type_files_by_subdir_name) {

      $filename_suffix = $type === 'theme_engine'
        ? '.engine'
        : '.' . $type;

      foreach ($type_files_by_subdir_name as $subdir_name => $subdir_files_by_name) {
        foreach ($subdir_files_by_name as $name => $yml_file) {

          // E.g. 'system.module'.
          $filename = $name . $filename_suffix;

          $extension = new Extension($this->root, $type, $yml_file, $filename);

          $extensions_grouped[$type][$subdir_name][$name] = $extension;
        }
      }
    }

    return $extensions_grouped;
  }
}
