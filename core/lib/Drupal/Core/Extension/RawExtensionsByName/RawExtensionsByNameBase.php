<?php

namespace Drupal\Core\Extension\RawExtensionsByName;

use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;

class RawExtensionsByNameBase implements RawExtensionsByNameInterface {

  /**
   * @var \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface
   */
  private $searchdirPrefixes;

  /**
   * @var \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface
   */
  private $searchdirToRawExtensionsGrouped;

  /**
   * @var string
   */
  private $type;

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped
   * @param string $type
   *   E.g. 'module' or 'theme_engine'.
   */
  public function __construct(SearchdirPrefixesInterface $searchdirPrefixes, SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped, $type) {
    $this->searchdirPrefixes = $searchdirPrefixes;
    $this->searchdirToRawExtensionsGrouped = $searchdirToRawExtensionsGrouped;
    $this->type = $type;
  }

  /**
   * Resets any stored or cached extension list.
   *
   * @return $this
   */
  public function reset() {
    // TODO: Implement reset() method.
  }

  /**
   * Returns all available extensions, with $extension->info possibly NOT yet
   * filled in.
   *
   * It can happen that other components further modify these objects, and add
   * the ->info array and more.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  public function getRawExtensions() {

    $files_by_name = [];
    foreach ($this->getSortedExtensionsBySearchdirPrefix() as $files_by_subdir_name) {
      foreach ($files_by_subdir_name as $subdir_files_by_name) {
        $files_by_name += $subdir_files_by_name;
      }
    }

    return $files_by_name;
  }

  /**
   * @return \Drupal\Core\Extension\Extension[][][]
   *   Format: $[$searchdir_prefix][$subdir_name][$extension_name] = $file
   *   E.g. $['core/']['modules']['system'] = 'core/modules/system/system.info.yml'
   */
  private function getSortedExtensionsBySearchdirPrefix() {

    $searchdir_prefix_weights = $this->getModifiedSearchdirPrefixWeights();
    $extensions_grouped_by_searchdir_prefix = $this->getExtensionsGroupedBySearchdirPrefix();

    // Make sure that arrays have same size for array_multisort().
    foreach (array_diff_key($searchdir_prefix_weights, $extensions_grouped_by_searchdir_prefix) as $searchdir_prefix => $_) {
      // This case might happen if some searchdir prefixes have no entries.
      $extensions_grouped_by_searchdir_prefix[$searchdir_prefix] = [];
    }
    foreach (array_diff_key($extensions_grouped_by_searchdir_prefix, $searchdir_prefix_weights) as $searchdir_prefix => $_) {
      // This case should not really happen.
      throw new \RuntimeException("Unexpected searchdir prefix '$searchdir_prefix'.");
    }

    array_multisort($searchdir_prefix_weights, SORT_DESC | SORT_NUMERIC, $extensions_grouped_by_searchdir_prefix);

    return $extensions_grouped_by_searchdir_prefix;
  }

  /**
   * @return int[]
   *   Format: $['core/'] = 0
   */
  protected function getModifiedSearchdirPrefixWeights() {
    return $this->searchdirPrefixes->getSearchdirPrefixWeights();
  }

  /**
   * @return \Drupal\Core\Extension\Extension[][][]
   *   Format: $[$searchdir_prefix][$subdir_name][$extension_name] = $file
   *   E.g. $['core/']['modules']['system'] = 'core/modules/system/system.info.yml'
   */
  protected function getExtensionsGroupedBySearchdirPrefix() {

    $extensions_grouped = [];
    foreach ($this->searchdirPrefixes->getSearchdirPrefixWeights() as $searchdir_prefix => $searchdir_weight) {
      $searchdir_extensions_by_type = $this->searchdirToRawExtensionsGrouped->getRawExtensionsGrouped($searchdir_prefix);
      if (array_key_exists($this->type, $searchdir_extensions_by_type)) {
        $extensions_grouped[$searchdir_prefix] = $searchdir_extensions_by_type[$this->type];
      }
    }

    return $extensions_grouped;
  }

}
