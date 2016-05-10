<?php

namespace Drupal\Core\Extension\SearchdirPrefixes;

/**
 * Implementation with a fixed list of search directory prefixes.
 *
 * Useful for testing.
 */
class SearchdirPrefixes_Fixed implements SearchdirPrefixesInterface {

  /**
   * @var int[]
   */
  private $searchdirPrefixWeights;

  /**
   * @param int[] $searchdirPrefixWeights
   */
  public function __construct(array $searchdirPrefixWeights) {
    $this->searchdirPrefixWeights = $searchdirPrefixWeights;
  }

  /**
   * @return int[]
   *   Format: $[$searchdir_prefix] = $searchdir_weight
   *   E.g. ['core/' => 0, '' => 1, 'sites/default' => 2]
   */
  public function getSearchdirPrefixWeights() {
    return $this->searchdirPrefixWeights;
  }
}
